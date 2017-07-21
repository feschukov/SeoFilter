<?php
/** @var modX $modx */
/** @var array $sources */

$snippets = array();

$tmp = array(
    'sfLink' => array(
        'file' => 'sflink',
        'description' => 'This snippet need params: rule_id and params for this rule_id. Snippet return link to SEO page. Read the docs.',
    ),
    'sfWord' => array(
        'file' => 'sfword',
        'description' => 'This snippet-modifier need params: input and options or field_id for find in sfDictionary. Snippet return array word(alias,value).',
    ),
);

foreach ($tmp as $k => $v) {
    /** @var modSnippet $snippet */
    $snippet = $modx->newObject('modSnippet');
    $snippet->fromArray(array(
        'id' => 0,
        'name' => $k,
        'description' => @$v['description'],
        'snippet' => getSnippetContent($sources['source_core'] . '/elements/snippets/snippet.' . $v['file'] . '.php'),
        'static' => BUILD_SNIPPET_STATIC,
        'source' => 1,
        'static_file' => 'core/components/' . PKG_NAME_LOWER . '/elements/snippets/snippet.' . $v['file'] . '.php',
    ), '', true, true);
    /** @noinspection PhpIncludeInspection */
    $properties = include $sources['build'] . 'properties/properties.' . $v['file'] . '.php';
    $snippet->setProperties($properties);

    $snippets[] = $snippet;
}
unset($tmp, $properties);

return $snippets;