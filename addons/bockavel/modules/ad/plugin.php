<?php defined('BASEPATH') or exit('No direct script access allowed');

class Plugin_Ad extends Plugin
{


    /**
     *
     * @return string
     */
    public function data()
    {
        //{{ Ad:data id="{{id}}" property="ad_title" }}

        $property = $this->attribute('property', 'id');

        $params = [
            'namespace' => 'streams',
            'stream' => 'ads',
            'where' => SITE_REF.'_ads.id=' .$this->attribute('id', 0)
        ];
        $data = ci()->streams->entries->get_entries($params)['entries'][0][$property];



        return $data?$data:'no ad data';
    }

    public function nl2br()
    {
        return nl2br($this->attribute('data', ''));
    }

    public function textLimit()
    {
        // {{ ad:textLimit text="{{ ad_body }}" limit="20" anchor="" }}

        $text = $this->attribute('text', '');
        $limit = $this->attribute('limit', 1000);
        $end = $this->attribute('end', '');
        $this->load->helper('text');

        if (strlen($end)>0 && strlen($text) > $limit){
                return character_limiter($text, $limit,'') . $end;
        }
        return character_limiter($text, $limit);

    }

    public function isNewAds()
    {
        //{{ if ad:isNewAds }}{{ endif }}
        $params = array(
            'stream'    => 'ads',
            'namespace' => 'streams',
            'where' =>SITE_REF."_ads.updated BETWEEN SUBDATE(CURDATE(), INTERVAL 2 MONTH) AND NOW()"
        );

        return ci()->streams->entries->get_entries($params)['total']>0;

    }

    public function show()
    {
        //Temporary function for feature development access
        return true;
        /*
         * {{ if ad:show }}{{ endif }}
         * {{ if { ad:show } }}
         * {{ if { ad:show user="5"} }}
                text
            {{ endif }}
         */
        if(!$this->current_user)
        {
            return false;
        }
        $acceptUsers=explode(',','1,49,66,99,72,43,45,87');
        //49 Anna Karin Gidund, soeren@gideget.se
        //66 Mikaela Janolsgården, janols.garden@yahoo.se
        //99 Tor Norman tor@bymejeriet.se
        //72 Robert Brook c.maria.brook@gmail.com
        //43 Elisabet Forsdal elisabet.forsdahl@telia.com
        //45 Michael Sjöman michael.sjoman@tele2.se
        //87 Malin Sala malin.sala@hotmail.se

        if(in_array($this->current_user->id,$acceptUsers)){
            return true;
        }
        return false;

    }

    public function spamFilter()
    {
        //Event trigger needed for this plugin

        return '<input type="text" name="subject"><script>$(function() {$("input[name=subject]").hide();});</script>';
    }

}

