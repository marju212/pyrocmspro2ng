<?php
class Temp_Lex extends Lex_Parser {
  
  function data()
  {
    return Lex_Parser::$data;
  }
  
  function callback_data()
  {
    return array_diff_key( Lex_Parser::$callback_data,Lex_Parser::$data );
  }
}
 
class Plugin_Vardump extends Plugin {
  
  var $res = '';

  function data()
  {
    $this->res = '';
    $this->_takedump(Temp_Lex::data());
    return $this->res;
  }

  function callback()
  {
    $this->res = '';
    $this->_takedump(Temp_Lex::callback_data());
    return $this->res;
  }


  function _takedump($data, $ident = '')
  {   
     if(is_array($data))
     {
       foreach($data as $k => $mixed)
       {  
          if( is_array($mixed) )
          {
             $this->_takedump($mixed, $ident.$k.'.'); 
          }
          elseif( is_object($mixed) )
          {
             $arr =  (array)get_object_vars($mixed);
             $this->_takedump($arr, $ident.$k.'.'); 
          }
          else
          {
            $this->res.= '<div>&#123;&#123;' .$ident.$k.'&#125;&#125;</div>';
          }          
       }
     }
  }
}
?>