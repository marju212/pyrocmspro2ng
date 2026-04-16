<?php

/**
 * Created by PhpStorm.
 * User: Marcus
 * Date: 2015-04-25
 * Time: 21:52
 */
class News extends Public_Controller
{

    public function index($newsSlug = '')
    {

        $result = $this->getNewsArticle($newsSlug);


        $this->template
            ->title('Nyheter', $result->title)
            ->build('news/index', $result);


    }


    private function getNewsArticle($newsSlug = '')
    {

        $params = [
            'namespace' => 'streams',
            'stream' => 'news',
            'where' => "title_slug='$newsSlug'"
        ];
        $result = ($result = ci()->streams->entries->get_entries($params)['entries']) ? $result[0] : null;


        if (!$result) {
            $params = [
                'namespace' => 'streams',
                'stream' => 'news',
                'where' => 'publish_date < now()',
                'order_by' => "publish_date",
                'limit' => 1
            ];
        }
        $result = ($result = ci()->streams->entries->get_entries($params)['entries']) ? $result[0] : null;


        return $result ? (object)$result : redirect('');

    }

}
