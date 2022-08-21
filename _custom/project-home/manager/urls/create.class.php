<?php

class sfUrlsCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'sfUrls';
    public $classKey = 'sfUrls';
    public $languageTopics = array('seofilter');
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('old_url'));
        $multi_id = trim($this->getProperty('multi_id'));
        if (empty($name)) {
            $this->modx->error->addField('old_url', $this->modx->lexicon('seofilter_url_err_url'));
        } elseif ($this->modx->getCount($this->classKey, array('old_url' => $name,'multi_id'=>$multi_id))) {
            $this->modx->error->addField('old_url', $this->modx->lexicon('seofilter_url_err_ae'));
        }



        return parent::beforeSet();
    }

    public function afterSave()
    {
        $path = $this->modx->getOption('seofilter_core_path', null, $this->modx->getOption('core_path') . 'components/seofilter/');
        //$this->modx->log(modX::LOG_LEVEL_ERROR,'ID: '.$this->object->get('id').print_r($this->getProperties(),1));
        $url_id = $this->object->get('id');
        $field_words = $this->getProperty('field_word');
        foreach($field_words as $fkey => $field_word) {
            $processorProps = array(
                'url_id' => $url_id,
                'field_id' => $field_word['field_id'],
                'word_id' => $field_word['word_id'],
                'priority' => $fkey,
            );
            $otherProps = array('processors_path' => $path . 'processors/');
            $response = $this->modx->runProcessor('mgr/urls/urlword/create', $processorProps, $otherProps);
            if ($response->isError()) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, '[SeoFilter] ' . $response->getMessage());
            }
        }

        return parent::afterSave();
    }


}

return 'sfUrlsCreateProcessor';