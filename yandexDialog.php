<?php

namespace frontend\modules\yandexDialog\controllers {

    use yii\web\Response;
    use common\modules\dayNews\models\DayNews;
    use yii\filters\VerbFilter;
    use Yii;

    final class IndexController extends \yii\web\Controller {

        public $content;
        public $enableCsrfValidation = false;

        public function behaviors() {
            return [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'index' => ['get', 'post'],
                    ],
                ],
            ];
        }

        public function actionIndex($regionId) {

            //получает данные из потока POST запроса
            $apiRequestArray = json_decode(trim(file_get_contents('php://input')), true);
            
            $model = DayNews::findTodayNewsByRegion($regionId);
			
            for ($i = 1; $i <= 6; $i++) {
                $newsNameArray[] = $model[$i]->news->name;
            }
            
            $newsHeaders = implode("\n", $newsNameArray);

            $arrayToEncode = [
                "response" => [
                    "text" => htmlspecialchars_decode($newsHeaders),
                    "end_session" => false,
                ],
                "session" => [
                    "session_id" => $apiRequestArray['session']['session_id'],
                    "message_id" => intval($apiRequestArray['session']['message_id']),
                    "user_id" => $apiRequestArray['session']['user_id'],
                ],
                "version" => $apiRequestArray['version']
            ];

            $this->content = json_encode($arrayToEncode);

            $response = \Yii::$app->getResponse();
            $response->getHeaders()->set('Content-Type', 'application/json');
            $response->getHeaders()->set('Access-Control-Allow-Origin', '*');
            $response->format = \yii\web\Response::FORMAT_RAW;
            $response->data = $this->content;
            $response->send();
        }

    }

}
