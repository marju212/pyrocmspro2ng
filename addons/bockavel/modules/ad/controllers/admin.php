<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Admin extends Admin_Controller
{
    protected $section = 'items';

    public function __construct()
    {
        parent::__construct();


        // We'll set the partials and metadata here since they're used everywhere
        // $this->template->append_js('module::admin.js')
        //    ->append_css('module::admin.css');
    }

    /**
     * List all items
     */
    public function index()
    {

    }

}
