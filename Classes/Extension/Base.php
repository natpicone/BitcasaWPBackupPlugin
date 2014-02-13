<?php
abstract class WPB2D_Extension_Base
{
    const TYPE_DEFAULT = 1;
    const TYPE_OUTPUT = 2;

    protected
        $dropbox,
        $dropbox_path,
        $config
        ;

    private $chunked_upload_threashold;

    public function __construct()
    {
        $this->dropbox = WPB2D_Factory::get('dropbox');
        $this->config  = WPB2D_Factory::get('config');
    }

    public function set_chunked_upload_threashold($threashold)
    {
        $this->chunked_upload_threashold = $threashold;

        return $this;
    }

    public function get_chunked_upload_threashold()
    {
        if ($this->chunked_upload_threashold !== null)
            return $this->chunked_upload_threashold;

        return CHUNKED_UPLOAD_THREASHOLD;
    }

    abstract public function complete();
    abstract public function failure();

    abstract public function get_menu();
    abstract public function get_type();

    abstract public function is_enabled();
    abstract public function set_enabled($bool);
}
