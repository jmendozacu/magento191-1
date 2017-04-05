<?php
N2Loader::import('libraries.plugins.N2SliderItemAbstract', 'smartslider');

class N2SSPluginItemImageArea extends N2SSPluginItemAbstract {

    public $_identifier = 'imagearea';

    protected $priority = 7;

    protected $layerProperties = array(
        "width"  => 150,
        "height" => 150
    );

    protected $group = 'Basic';

    public function __construct() {
        $this->_title = n2_x('Image area', 'Slide item');
    }

    public function getTemplate($slider) {
        return '<div style="display:inline-block;vertical-align:top;width:100%;height:100%;background: url({image}) no-repeat;background-size:{fillmode};background-position: {positionx}% {positiony}%;"></div>';
    }

    function _renderAdmin($data, $itemId, $slider, $slide) {
        return $this->getHtml($data, $itemId, $slider, $slide);
    }

    function _render($data, $itemId, $slider, $slide) {

        return $this->getLink($slide, $data, $this->getHtml($data, $itemId, $slider, $slide), array(
            'style' => 'display: block; width:100%;height:100%;',
            'class' => 'n2-ow'
        ));
    }

    private function getHtml($data, $id, $slider, $slide) {
        $width  = '100%';
        $height = '100%';

        $_width = $data->get('width');
        if (!empty($_width)) {
            $width = $_width . 'px';
        }

        $_height = $data->get('height');
        if (!empty($_height)) {
            $height = $_height . 'px';
        }

        return N2Html::tag('span', array(
            'style' => 'display:inline-block;vertical-align:top;width:100%;height:100%;background: url(' . N2ImageHelper::fixed($slide->fill($data->get('image', ''))) . ') no-repeat;background-size:' . $data->get('fillmode', 'cover') . ';background-position: ' . $data->get('positionx', 50) . '% ' . $data->get('positiony', 50) . '%;'
        ));
    }

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->_identifier . DIRECTORY_SEPARATOR;
    }

    function getValues() {
        return array(
            'image'     => '$system$/images/placeholder/image.png',
            'link'      => '#|*|_self',
            'fillmode'  => 'cover',
            'positionx' => 50,
            'positiony' => 50
        );
    }

    public function prepareExport($export, $data) {
        $export->addImage($data->get('image'));
        $export->addLightbox($data->get('link'));
    }

    public function prepareImport($import, $data) {
        $data->set('image', $import->fixImage($data->get('image')));
        $data->set('link', $import->fixLightbox($data->get('link')));
        return $data;
    }
}

N2Plugin::addPlugin('ssitem', 'N2SSPluginItemImageArea');
