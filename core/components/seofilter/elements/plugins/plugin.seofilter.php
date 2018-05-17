<?php
/** @var modX $modx */
/** @var array $scriptProperties */
switch ($modx->event->name) {
    case 'OnLoadWebDocument':
        if($page = $modx->resource->id) {
            $modx->addPackage('seofilter', $modx->getOption('core_path').'components/seofilter/model/');
            $proMode = $modx->getOption('seofilter_pro_mode',null,0,true);
            $q = $modx->newQuery('sfRule');
            $q->where(array('active'=>1));
            if($proMode) {
                $q->where('1=1 AND FIND_IN_SET('.$page.',REPLACE(IFNULL(NULLIF(pages,""),page)," ",""))');
            } else {
                $q->where(array('page' => $page));
            }
            if($modx->getCount('sfRule',$q)) {
                $SeoFilter = $modx->getService('seofilter', 'SeoFilter', $modx->getOption('seofilter_core_path', null,
                        $modx->getOption('core_path') . 'components/seofilter/') . 'model/seofilter/', $scriptProperties);
                if (!($SeoFilter instanceof SeoFilter)) break;
                if(!$SeoFilter->initialized[$modx->resource->context_key]) {
                    $SeoFilter->initialize($modx->resource->context_key, array('page' => (int)$page));
                }
            }
        }
        break;
    case 'OnBeforeDocFormSave':
        $sf_count = $modx->getOption('seofilter_count', null, 0, true);
        if(!$sf_count)
            break;
        $sf_classes = $modx->getOption('seofilter_classes', null, 'msProduct', true);
        if($sf_classes &&!in_array($resource->get('class_key'),array_map('trim', explode(',',$sf_classes))))
            break;
        $sf_templates = $modx->getOption('seofilter_templates', null, '', true);
        if($sf_templates && !in_array($resource->get('template'),array_map('trim', explode(',',$sf_templates))))
            break;

        $before = array();
        if ($mode == 'upd') {
            $SeoFilter = $modx->getService('seofilter', 'SeoFilter', $modx->getOption('seofilter_core_path', null,
                    $modx->getOption('core_path') . 'components/seofilter/') . 'model/seofilter/', $scriptProperties);
            if (!($SeoFilter instanceof SeoFilter)) break;

            $modx->addPackage('seofilter', $modx->getOption('core_path').'components/seofilter/model/');

            $fields = $SeoFilter->getFieldsKey();
            $before = $SeoFilter->getResourceData($resource->id,$fields);
        }
        $_SESSION['SeoFilter']['before'] = $before;
        @session_write_close();
        break;
    case 'OnDocFormSave':
        $sf_classes = $modx->getOption('seofilter_classes', null, 'msProduct', true);
        if($sf_classes &&!in_array($resource->get('class_key'),array_map('trim', explode(',',$sf_classes))))
            break;
        $sf_templates = $modx->getOption('seofilter_templates', null, '', true);
        if($sf_templates && !in_array($resource->get('template'),array_map('trim', explode(',',$sf_templates))))
            break;

        $sf_count = $modx->getOption('seofilter_count', null, 0, true);
        $SeoFilter = $modx->getService('seofilter', 'SeoFilter', $modx->getOption('seofilter_core_path', null,
                $modx->getOption('core_path') . 'components/seofilter/') . 'model/seofilter/', $scriptProperties);
        $pdo = $SeoFilter->pdo;
        if (!($SeoFilter instanceof SeoFilter) && !($pdo instanceof pdoFetch)) break;

        $dictionary = $changes = $before = array();
        $fields = $SeoFilter->getFieldsKey();
        $after = $SeoFilter->getResourceData($resource->id, $fields);
        if($sf_count) {
            $SeoFilter->loadHandler();
            $before = $_SESSION['SeoFilter']['before'];
            unset($_SESSION['SeoFilter']);
        }

        foreach(array('tvs','tvss','data') as $var) {
            $dictionary = $SeoFilter->returnChanges($after[$var],array(),$var);

            if(!empty($after[$var])) {
                if(!empty($before[$var])) {
                    if($change = $SeoFilter->returnChanges($before[$var],$after[$var],$var))
                        $changes[$var] = $change;
                    // сравнить значения
                } else {
                    if($change = $SeoFilter->returnChanges($after[$var],array(),$var))
                        $changes[$var] = $change;
                    // появились значения
                }
            } elseif(!empty($before[$var])) {
                if($change = $SeoFilter->returnChanges($before[$var],array(),$var))
                    $changes[$var] = $change;
                // удалилась значения
            }


            if(!empty($dictionary)) {
                foreach ($dictionary as $field_key => $words) {
                    foreach($words as $word) {
                        if(empty($word)) {
                            continue;
                        }
                        $slider = (int)$fields[$var][$field_key]['slider'];
                        if($word_array = $SeoFilter->getWordArray($word, $fields[$var][$field_key]['id'], 0, !$slider)) {
                            if($sf_count && is_array($changes[$var][$field_key]) && in_array($word,$changes[$var][$field_key])) {
                                $recount = $SeoFilter->countHandler->countByWord($word_array['id']);
                            }
                        } elseif($sf_count && $slider && is_array($changes[$var][$field_key]) && in_array($word,$changes[$var][$field_key])) {
                            $recount = $SeoFilter->countHandler->countBySlider($fields[$var][$field_key]['id'],$fields[$var][$field_key]);
                        }
                        if(is_array($changes[$var][$field_key])) {
                            foreach ($changes[$var][$field_key] as $key => $value) {
                                if ($word == $value) {
                                    unset($changes[$var][$field_key][$key]);
                                }
                                //здесь пересчитали только новые значения
                            }
                        }

                    }
                }
            }

            if(!empty($changes[$var]) && $sf_count) {
                // действия на пересчёт слов, которые удалены
                foreach($changes[$var] as $field_key => $words) {
                    foreach($words as $_word) {
                        if(is_array($_word)) {
                            $word_arr = $_word;
                        }  else {
                            $word_arr = array($_word);
                        }
                        foreach($word_arr as $word) {
                            if (empty($word)) {
                                continue;
                            }
                            $slider = (int)$fields[$var][$field_key]['slider'];
                            if ($word_array = $SeoFilter->getWordArray($word, $fields[$var][$field_key]['id'], 0, !$slider)) {
                                $recount = $SeoFilter->countHandler->countByWord($word_array['id']);
                            } elseif ($slider) {
                                $recount = $SeoFilter->countHandler->countBySlider($fields[$var][$field_key]['id'], $fields[$var][$field_key]);
                            }
                        }
                    }
                }
            }
        }


        break;
    case 'OnPageNotFound':
        $time = microtime(true);
        $alias = $del_get = $modx->context->getOption('request_param_alias', 'q');

        if (isset($_REQUEST[$alias])) {
            /** @var SeoFilter $SeoFilter */
            $SeoFilter = $modx->getService('seofilter', 'SeoFilter', $modx->getOption('seofilter_core_path', null,
                    $modx->getOption('core_path') . 'components/seofilter/') . 'model/seofilter/', $scriptProperties);
            $pdo = $SeoFilter->pdo;
            if (!($SeoFilter instanceof SeoFilter) && !($pdo instanceof pdoFetch)) break;

            $container_suffix = $SeoFilter->config['container_suffix'];
            $url_suffix = $SeoFilter->config['url_suffix'];
            $url_redirect = $SeoFilter->config['redirect'];

            $base_get = array_map('trim', explode(',',$SeoFilter->config['base_get']));
            $separator = $SeoFilter->config['separator'];
            $site_start = $SeoFilter->config['site_start'];
            $charset = $SeoFilter->config['charset'];
            $check = $novalue = $page = $fast_search = 0; //переменные для проверки
            $params = array(); //итоговый массив с параметром и значением
            $last_char = ''; //был ли в конце url-а слэш
            $ctx = $modx->context->key; //если используете контексты, то переключить должны до события onPageNotFound

//            if (substr($_REQUEST[$alias], -1) == '/') {
//                $last_char = '/';
//            }

            $request = trim($_REQUEST[$alias]);

            if ($url_suffix) {
                if (strpos($request, $url_suffix, strlen($request) - strlen($url_suffix))) {
                    $request = substr($request, 0, -strlen($url_suffix));
                    $last_char = $url_suffix; //был ли суффикс в конце

                }
            } elseif ($url_redirect) {
                if (substr($_REQUEST[$alias], -1) == '/') {
                    $last_char = '/';
                }
            }



//            print_r($request);

            $request = trim($request, "/"); //основной запрос
            $tmp = explode('/', $request); //массив запроса

            $check_doubles = false;
            $uris = $aliases = array();

            $q = $modx->newQuery('sfRule');
            $q->where(array('active'=>1));
            $q->select(array('sfRule.*'));
            $page_ids = array();
            $all_pages = array();
            if ($q->prepare() && $q->stmt->execute()) {
                while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                    if($SeoFilter->config['proMode']) {
                        $pages = $row['pages'];
                        if(empty($pages)) {
                            $pages = $row['page'];
                        }
                    } else {
                        $pages = $row['page'];
                        if(empty($pages) && !empty($row['pages'])) {
                            $pages = $row['pages'];
                        }
                    }
                    $pages = array_map('trim',explode(',',$pages));
                    $page_ids[$row['id']] = $pages;
                    $all_pages = array_merge($all_pages,$pages);
                }
            }
            $all_pages = array_unique($all_pages);

            $q = $modx->newQuery('modResource');
            $q->where(array(
                'id:IN'=>$all_pages,
                'deleted'=>0,
                'published'=>1,
                'context_key' => $ctx
            ));
            $q->select(array(
                'modResource.id,modResource.alias,modResource.uri,modResource.uri_override'
            ));
            if($q->prepare() && $q->stmt->execute()) {
                while($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                    $uri = $SeoFilter->clearSuffixes($row['uri']);
                    if($row['id'] == $site_start) {
                        $uri = '';
                    }
                    $uris[$row['id']] = array_reverse(explode('/',$uri),1);
                    //переворот для удобства поиска

                    $alias = $row['alias'];
                    if($row['uri_override']) {
                        //если url заморожен
                        $uri = explode('/',$uri);
                        $alias = array_pop($uri);
                    }

                    if(in_array($alias,$aliases)) {
                        $check_doubles = true;
                    }
                    $aliases[$row['id']] = $alias;
                }
            }

            $r_tmp = array_reverse($tmp, 1); //перевёрнутый запрос

            if($check_doubles) {
                //если есть дубли синонимов

                //обязательная сортировка массива по количеству внутренних алиасов
                uasort($uris, function($a, $b) {
                    if (count($a) == count($b)) {
                        return 0;
                    }
                    return (count($a) > count($b)) ? -1 : 1;
                });

                foreach ($uris as $page_id => $uri_arr) {
                    $need_count = count($uri_arr); //сколько совпадений подряд нужно
                    $uri_count = 0; //количество совпадений
                    $pos_count = false; //позиция, на которой произошло сопадение
                    $check_break = false; //проверка, чтобы в разнобой не пошло
                    foreach($r_tmp as $t_key => $t_alias) {
                        foreach($uri_arr as $u_key => $uri) {
                            if($uri == $t_alias) {
                                if($pos_count !== false) {
                                    if($pos_count-$uri_count != $t_key) {
                                        $check_break = true;
                                        break;
                                    }
                                } else {
                                    $pos_count = $t_key;
                                }
                                $uri_count++;
                                break; //выходим из перебора uri для текущего alias-а в адресе
                            }
                        }
                        if($check_break) {
                            break;
                        }
                    }
                    if($need_count == $uri_count) {
                        //ссылка найдена
                        $page = $page_id;
                        $tmp = array_slice($tmp,++$pos_count);
                        break;
                    }
                }
            } else {
                //простой механизм поиска
                $tmp_id = 0;

                foreach ($r_tmp as $t_key => $t_alias) {
                    if ($page = array_search($t_alias, $aliases)) {
                        $tmp_id = $t_key;
                        break;
                    }
                }

                if ($page) {
                    for ($i = 0; $i <= $tmp_id; $i++) {
                        array_shift($tmp);
                    }
                }
            }

            if(!$page) {
                //если не будет найдено, то проверим, вдруг это главная страница
                if(in_array($site_start,array_keys($aliases))) {
                    $page = $site_start;
                }
            }

            if($page) {
//                $p = $modx->newQuery('modResource',array('id'=>$page));
//                $p->select('context_key');
//                $ctx = $modx->getValue($p->prepare());

                if($page == $site_start) {
                    $url = '';
                } else {
                    $url = $modx->makeUrl($page,$ctx,'',-1);
                }
                if(strpos($url,$modx->getOption('site_url')) !== false) {
                    $url = str_replace($modx->getOption('site_url'),'',$url);
                }
                if($c_suffix = $SeoFilter->config['container_suffix']) {
                    if(strpos($url,$c_suffix,strlen($url)-strlen($c_suffix)) !== false) {
                        $url = substr($url,0,-strlen($c_suffix));
                    }
                }
                foreach($SeoFilter->config['possibleSuffixes'] as $possibleSuffix) {
                    if (substr($url, -strlen($possibleSuffix)) == $possibleSuffix) {
                        $url = substr($url, 0, -strlen($possibleSuffix));
                    }
                }
                if(implode('/',array_reverse(array_diff($r_tmp,$tmp))) != trim($url,'/')) {
                    break;
                }


                if($tmp && $url_array = $SeoFilter->findUrlArray(implode('/',$tmp),$page)) {
                    if($url_array['active']) {
                        $old_url = $url_array['old_url'];
                        $new_url = $url_array['new_url'];
                        $rule_id = $url_array['multi_id'];

                        if ($new_url && ($new_url != implode('/', $tmp))) {
                            if ($container_suffix) {
                                if (strpos($url, $container_suffix, strlen($url) - strlen($container_suffix))) {
                                    $url = substr($url, 0, -strlen($container_suffix));
                                }
                            }
                            $modx->sendRedirect($url .'/'. $new_url . $url_suffix,false,'REDIRECT_HEADER','HTTP/1.1 301 Moved Permanently');
                        } elseif($url_redirect && ($url_suffix != $last_char)) {
                            if ($container_suffix) {
                                if (strpos($url, $container_suffix, strlen($url) - strlen($container_suffix))) {
                                    $url = substr($url, 0, -strlen($container_suffix));
                                }
                            }
                            $modx->sendRedirect($url .'/'. implode('/', $tmp) . $url_suffix,false,'REDIRECT_HEADER','HTTP/1.1 301 Moved Permanently');
                        }

                        $tmp = explode('/', $old_url);
                        $menutitle = '';
                        if ($url_array['menu_on']) {
                            $menutitle = $url_array['menutitle'];
                        }


                        $q = $modx->newQuery('sfUrlWord');
                        $q->sortby('priority','ASC');
                        $q->leftJoin('sfField','sfField','sfUrlWord.field_id = sfField.id');
                        $q->leftJoin('sfDictionary','sfDictionary','sfUrlWord.word_id = sfDictionary.id');
                        $q->where(array('sfUrlWord.url_id'=>$url_array['id']));
                        $q->select('sfUrlWord.id, sfField.id as field_id, sfField.alias as field_alias, sfDictionary.value as word_value, sfDictionary.input as word_input, sfDictionary.alias as word_alias');
                        if($q->prepare() && $q->stmt->execute()) {
                            while($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                                $_GET[$row['field_alias']] = $_REQUEST[$row['field_alias']] = $params[$row['field_alias']] = $row['word_input'];
                                if (!$menutitle) $menutitle = $row['word_value'];
                            }
                        }

                        $q = $modx->newQuery('sfFieldIds');
                        $q->where(array('multi_id'=>$rule_id));
                        $url_fields = $modx->getCount('sfFieldIds',$q);

                        if ((count($params) != $url_fields)) { //Доп проверка на изменения в базе
                            if($links = $pdo->getCollection('sfFieldIds', array('multi_id' => $rule_id), array('sortby' => 'priority'))) {
                                if (count($tmp) == count($links)) {  //дополнительная проверка на количество параметров в адресе и пересечении
                                    foreach ($links as $lkey => $link) {
                                        if ($field = $pdo->getArray('sfField', $link['field_id'])) {
                                            $alias = $field['alias'];
                                            if ($field['hideparam']) {
                                                if ($word = $pdo->getArray('sfDictionary', array('alias' => $tmp[$lkey]))) {
                                                    $_GET[$alias] = $_REQUEST[$alias] = $params[$alias] = $word['input'];
                                                    if (!$menutitle) $menutitle = $word['value'];
                                                }
                                            } else {
                                                $tmp_arr = explode($separator, $tmp[$lkey]);
                                                $word_alias = '';
                                                if ($field['valuefirst']) {
                                                    $del = array_pop($tmp_arr);
                                                    if ($del == $alias) {
                                                        $word_alias = implode($separator, $tmp_arr);
                                                    }
                                                } else {
                                                    $del = array_shift($tmp_arr);
                                                    if ($del == $alias) {
                                                        $word_alias = implode($separator, $tmp_arr);
                                                    }
                                                }
                                                if ($word_alias && $word = $pdo->getArray('sfDictionary', array('alias' => $word_alias, 'field_id' => $field['id']))) {
                                                    $_GET[$alias] = $_REQUEST[$alias] = $params[$alias] = $word['input'];
                                                    if (!$menutitle) $menutitle = $word['value'];
                                                }

                                            }
                                        }
                                    }
                                }
                            }
                        }


                        if (count($params)) {
                            $original_params = array_diff_key(array_merge($params,$_GET),array_flip(array_merge(array($del_get),$base_get)));
                            $fast_search = true;
                            $meta = $SeoFilter->getRuleMeta($params, $rule_id, $page, 0,0,$original_params);
                            if($SeoFilter->config['hideEmpty'] && $SeoFilter->config['count_childrens'] && !$meta['total']) {
                                $modx->setPlaceholder('sf.seo_id',$url_array['id']);
                                break;
                            }

                            if($SeoFilter->config['lastModified']) {
                                if(empty($meta['editedon']) && $meta['editedon'] != '0000-00-00 00:00:00') {
                                    $modified = $meta['editedon'];
                                } else {
                                    $modified = $meta['createdon'];
                                }
                                $modified = date('r',strtotime($modified));
                                $qtime = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : '';
                                if (strtotime($qtime) >= strtotime($modified)) {
                                    header ("HTTP/1.1 304 Not Modified ");
                                    exit();
                                }
                                header ("Last-Modified: $modified");
                            }

                            $SeoFilter->initialize($ctx, array('page' => $page, 'params' => $params));

                            if(is_dir($modx->getOption('core_path').'components/msvendorcollections/model/')) {
                                if ($msVC = $modx->getService('msvendorcollections', 'msVendorCollections', $modx->getOption('msvendorcollections_core_path', null,
                                        $modx->getOption('core_path') . 'components/msvendorcollections/') . 'model/msvendorcollections/', $scriptProperties)) {
                                    if (!$msVC->initialized[$modx->resource->context_key]) {
                                        $msVC->initialize($modx->resource->context_key);
                                    }
                                }
                            }


                            $meta['menutitle'] = $menutitle;
                            if(isset($meta['properties'])) {
                                $meta['properties'] = $modx->toJSON($meta['properties']);
                            }
                            if(isset($meta['introtexts'])) {
                                $meta['introtexts'] = $modx->toJSON($meta['introtexts']);
                            }
                            if(isset($meta['url'])) {
                                $meta['url'] .= $SeoFilter->config['url_suffix'];
                            }

                            if($ctx != 'web') {
                                $modx->switchContext($ctx);
                            }

                            $meta['params'] = $modx->toJSON($params);

                            $modx->setPlaceholders($meta, 'sf.');
                            $modx->sendForward($page);
                        } else {
                            if ($url = $modx->getObject('sfUrls', array('page_id' => $page, 'old_url' => $old_url, 'multi_id' => $rule_id))) {
                                $url->set('active', 0);
                                $url->save();
                            }
                        }

                    } else {
                        $modx->setPlaceholder('sf.seo_id',$url_array['id']);
                    }
                }
            }
        }
        break;
}