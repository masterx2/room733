<?php
require '../libs/safemysql.class.php';

// create table posts (id int(11) auto_increment primary key not null, cdate timestamp not null, title text not null, body longtext not null, author text);

class BlogEngine {
    private $dbtable;
    private $db;

    public function __construct() {
        $this->dbtable = 'posts';
        $opts = [
            'user' => 'root',
            'pass' => 'sqlsadizm',
            'db' => 'room',
            'charset' => 'cp1251'
        ];
        $this->db = new SafeMySQL($opts);
    }

    public function addPost($title=null, $body=null, $author=null) {
        if (isset($title) && isset($body) && isset($author)) {
            return $this->db->query('insert into ?n (title, body, author) values(?s, ?s, ?s)',
                $this->dbtable, $title, $body, $author);
        }
    }

    public function getPosts($limit=20, $page=1, $reverse=true) {
        $from = ($limit*$page)-$limit;
        $sort = $reverse ? 'desc' : null;
        $posts = $this->db->getAll('select * from ?n order by id ?p limit ?i, ?i',
            $this->dbtable, $sort, $from, $limit);
        return $posts;
    }

    public function getPostsCount() {
        return $this->db->getOne('select count(*) from ?n',$this->dbtable);
    }

    public function deletePost($id) {
        return $this->db->query('delete from ?n where id = ?i', $this->dbtable, $id);
    }
}

$app = new BlogEngine();
// echo $app->addPost('Title', 'Body', 'Author');
// echo $app->getPostsCount();

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'addpost':
            if (isset($_POST['title']) && isset($_POST['body']) && isset($_POST['author'])) {
                $app->addPost($_POST['title'], $_POST['body'], $_POST['author']);
            }
            break;
        case 'delpost':
            if (isset($_POST['id'])) {
                echo $app->deletePost($_POST['id']);
                die();
            }
    }
}

$posts = $app->getPosts();

?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Room 733</title>
    <script src="/js/jquery.min.js"></script>
    <script src="/js/jquery.selection.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/core.js"></script>
    <link href='http://fonts.googleapis.com/css?family=PT+Sans+Narrow&subset=cyrillic-ext,latin' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/css/bootstrap.min.css"/>
<!--    <link rel="stylesheet" href="/css/bootstrap-theme.min.css"/>-->
    <link rel="stylesheet" href="/css/style.css"/>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Room 733 <small>/var/log/room733</small></h1>
        </div>
        <div class="row">
            <div class="col-md-offset-3 col-md-6 col-xs-offset-1 col-xs-10">
                <div id="form-add">
                    <form class="form-horizontal" role="form" method="POST" action="/index.php" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="title">Заголовок</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Можно оставить пустым">
                        </div>
                        <div class="form-group">
                            <label for="title">Автор</label>
                            <input type="text" class="form-control" id="author" name="author" placeholder="Должно было заполниться при авторизации">
                        </div>
                        <div class="form-group">
                            <label for="title">Сообщение</label>
                            <button type="button" id="addimg" class="btn btn-default btn-xs">&lt;img&gt;</button>
                            <textarea class="form-control" id="body" rows="5" name="body" placeholder="My true story..."></textarea>
                        </div>
                        <button type="submit" id="addPost" class="btn btn-lg btn-default">Запостить</button>
                        <input type="hidden" name="action" value="addpost"/>
                    </form>
                </div>
                <div id="activate">Добавить</div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="feed">
                    <?php foreach ($posts as $post): ?>
                        <div class="post">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <div class="panel-title"><?php echo htmlspecialchars($post['title']); ?></div>
                                    <div class="deletePost" postid="<?=$post['id']?>">X</div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-body">
                                    <?php
                                    echo str_replace(['<script>','<style>','</script>','</style>'],'', $post['body']);
                                    ?>
                                </div>
                                <div class="panel-footer"><?php echo htmlspecialchars($post['cdate']);?> <?php echo htmlspecialchars($post['author']);?></div>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function (d, w, c) {
            (w[c] = w[c] || []).push(function() {
                try {
                    w.yaCounter26116719 = new Ya.Metrika({id:26116719,
                        webvisor:true,
                        clickmap:true,
                        trackLinks:true,
                        accurateTrackBounce:true});
                } catch(e) { }
            });

            var n = d.getElementsByTagName("script")[0],
                s = d.createElement("script"),
                f = function () { n.parentNode.insertBefore(s, n); };
            s.type = "text/javascript";
            s.async = true;
            s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

            if (w.opera == "[object Opera]") {
                d.addEventListener("DOMContentLoaded", f, false);
            } else { f(); }
        })(document, window, "yandex_metrika_callbacks");
    </script>
    <noscript><div><img src="//mc.yandex.ru/watch/26116719" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
</body>
</html>