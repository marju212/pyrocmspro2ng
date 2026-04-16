<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Ad extends Public_Controller
{
    public function __construct()
    {
        parent::__construct();


    }
    public function index()
    {

        is_logged_in() || redirect();




        $mode=' mode="new" ';
        $this->template
            ->title('Mina Annonser')
            ->build('index',compact(['mode','hasEntries']));
    }
    public function edit($id)
    {
        is_logged_in() || redirect();

        $mode=' creator_only="yes" mode="edit" edit_id="'.$id.'" ';

        $params = array(
            'stream'    => 'ads',
            'namespace' => 'streams',
            'where' =>SITE_REF."_ads.created_by='".ci()->current_user->user_id."' AND ".SITE_REF."_ads.id='".$id."'"
        );

            if ($this->streams->entries->get_entries($params)['entries'][0]['id']!=$id){
                $this->session->set_flashdata('error', 'Annonsen finns inte');
                redirect('ad/index');
            }





        $this->template
            ->title('Redigera annons')
            ->build('form',compact('mode'));
    }
    public function create()
    {
        is_logged_in() || redirect();
        // set the pagination limit
//        {{ member:registered_email }}
//        {{ member:registered_name }}
        $mode=' mode="new"';
        $this->template
            ->title('Skapa ny annons')
            ->build('form',compact('mode'));
    }
    public function show($id)
    {

        //check if still valid
        if ($id=='all' ){
            $this->template->build('none');
            return;
        }


        $this->template
            ->title('Visar annons')
            ->build($this->adExist($id)?'show':'notvalid',compact('id'));
    }
    public function delete($id)
    {
        is_logged_in() || redirect();
        $params = array(
            'stream'    => 'ads',
            'namespace' => 'streams',
            'where' =>SITE_REF."_ads.created_by='".ci()->current_user->user_id."' AND ".SITE_REF."_ads.id='".$id."'"
        );
        if (isset($this->streams->entries->get_entries($params)['entries'][0]['id'])){
            $this->streams->entries->get_entries($params)['entries'][0]['id']==$id OR die('Not allowed');
        }


        //check if still valid

        $this->streams->entries->delete_entry($id,'ads','streams');
        $this->session->set_flashdata('success', 'Annonsen raderad');
        redirect('ad/index');




    }
    public function contact($id)
    {

        //check if still valid
        $params = array(
            'stream'    => 'ads',
            'namespace' => 'streams',
            'where' =>SITE_REF."_ads.id='".$id."'");
        if (isset($this->streams->entries->get_entries($params)['entries'][0]['id'])){
            $ad_creator=$this->streams->entries->get_entries($params)['entries'][0]['created_by']['email'];
            $ad_title=$this->streams->entries->get_entries($params)['entries'][0]['ad_title'];


        }
$contact=true;
        $this->template
            ->title('Kontakta annonsören')
            ->build($this->adExist($id)?'show':'notvalid',compact(['id','contact','ad_creator','ad_title']));
    }

    private function adExist($id)
    {

        $params = [
            'stream'    => 'ads',
            'namespace' => 'streams',
            "where" => SITE_REF."_ads.id='".$id."'"
        ];
        return count($this->streams->entries->get_entries($params)['entries']) > 0;


    }

}
