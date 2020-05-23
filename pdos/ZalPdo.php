<?php

//READ
function getPicks($categoryId)
{
    $pdo = pdoSqlConnect();
    $query = "select id                                                  categoryId,
       case
           when (name = '홈카페') then '잘먹 pick! 홈카페'
           when (name = '홈쿠킹') then '잘먹 pick! 홈쿠킹'
           when (name = '홈트레이닝') then '잘살 pick! 홈트레이닝' end categoryTitle
from category
where id =?;";

    $st = $pdo->prepare($query);
    $st->execute([$categoryId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function getSpecificPicks($categoryId)
{
    $pdo = pdoSqlConnect();
    $query = "select id pickId, image_url imgUrl, title, subtitle subTitle
from pick
where category_id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$categoryId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getUserId($userEmail)
{
    $pdo = pdoSqlConnect();
    $query = "select id from user where email = ?";

    $st = $pdo->prepare($query);
    $st->execute([$userEmail]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['id'];
}

function getSubImgUrl($pickId)
{
    $pdo = pdoSqlConnect();
    $query = "select sub_image_url imgUrl from pick where id = ?;
";

    $st = $pdo->prepare($query);
    $st->execute([$pickId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];

}

function getVideos($userId, $pickId)
{
    $pdo = pdoSqlConnect();
    $query = "select id videoId,
       url, title, publisher, content,
       IF(h.status is null, 'N', status) heart
       from video
LEFT JOIN heart h on video.id = h.video_id and user_id = ?
where pick_id=?;
";

    $st = $pdo->prepare($query);
    $st->execute([$userId, $pickId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

//    print_r($res);

    foreach ($res as $key => $value) {

        $res[$key]['keywords'] = getVideoKeywords($res[$key]['videoId']);
        $res[$key]['thumbnail'] = "http://img.youtube.com/vi/" . $res[$key]['url'] . "/0.jpg";

    }
    // $temp3['keywords'] = getVideoKeywords($temp2['videos']['videoId']);

    $st = null;
    $pdo = null;

    return $res;

}

function isExistPick($pickId)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM pick p WHERE p.id= ?) AS exist;";


    $st = $pdo->prepare($query);
    $st->execute([$pickId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function getLikes($userId, $category)
{

    $pdo = pdoSqlConnect();
    $query = "select IF(CATEGORY.name is null, '',CATEGORY.name) name, id videoId,
       url, title, publisher, content,
       IF(h.status is null, 'N', status) heart
       from video
LEFT JOIN heart h on video.id = h.video_id and user_id = ?
LEFT JOIN (select c.name, v.id videoId
from video v
         LEFT JOIN pick p on v.pick_id = p.id
         JOIN category c on p.category_id = c.id) CATEGORY ON CATEGORY.videoId = video.id
where status = 'Y'";

    $query = $query . $category . ";";

//    echo $query;

    $st = $pdo->prepare($query);
    $st->execute([$userId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    foreach ($res as $key => $value) {

        $res[$key]['keywords'] = getVideoKeywords($res[$key]['videoId']);
        $res[$key]['thumbnail'] = "http://img.youtube.com/vi/" . $res[$key]['url'] . "/0.jpg";

    }
    $st = null;
    $pdo = null;

    return $res;
}

function isExistVideo($videoId)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM video v WHERE v.id= ?) AS exist;";


    $st = $pdo->prepare($query);
    $st->execute([$videoId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function getVideoKeywords($videoId)
{

    $pdo = pdoSqlConnect();
    $query = "select concat('#', word) word  from keyword where video_id =?";

    $st = $pdo->prepare($query);
    $st->execute([$videoId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
//    print_r($res);
    $keywords = '';


    foreach ($res as $key => $value) {

        $keywords = $keywords . $res[$key]['word'] . ' ';
    }
//    foreach ($res as $key => $value){
//
//
//    }
//    $keywords = implode(" ", $res);
//    echo $keywords;

    $st = null;
    $pdo = null;

    return $keywords;
}

function getVideo($userId, $videoId)
{

    $pdo = pdoSqlConnect();
    $query = "select
       url, title, publisher,
       IF(h.status is null, 'N', status) heart,
       content
       from video
LEFT JOIN heart h on video.id = h.video_id and user_id = ?
where id = ?;";


    $st = $pdo->prepare($query);
    $st->execute([$userId, $videoId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}


function isExistLike($userId, $videoId)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM heart h WHERE h.user_id=? and h.video_id=?) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userId, $videoId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function postHeart($userId, $videoId, $status)
{
    $pdo = pdoSqlConnect();
    $insertQuery = "INSERT INTO heart (user_id, video_id, status) VALUES (?, ?, 'Y');";
    $updateQuery = "UPDATE heart SET status = IF(status = 'Y', 'N', 'Y')  WHERE user_id =? and video_id =?;";

    $query = "";

    if ($status == 'insert') {
        $query = $insertQuery;
    } elseif ($status == 'update') {
        $query = $updateQuery;
    }

    $st = $pdo->prepare($query);
//    echo $query;
//    echo $userId . $videoId;
    $st->execute([$userId, $videoId]);

    $st = null;
    $pdo = null;

}


function userHeartStatus($userId, $videoId)
{

    $pdo = pdoSqlConnect();
    $query = "select IF(status = 'N', 'N', 'Y') status
from heart
where user_id =? and video_id = ?";

    $st = $pdo->prepare($query);
    $st->execute([$userId, $videoId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];

}


// 4. 내취향
function getSubCategory()
{

    $pdo = pdoSqlConnect();
    $query = "select name from sub_category;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

//    print_r($res);

    $real = Array();
    foreach ($res as $key => $value) {
        array_push($real, $res[$key]['name']);
    }
//    print_r($real);
    $st = null;
    $pdo = null;

    return $real;

}

function getPreference($userId, $keyword, $kindArray)
{

    // 내 취향 insert 하고 + select 해오는 함수
    $pdo = pdoSqlConnect();

    # keywords = ('빵', '양식')
    try {

        $pdo->beginTransaction();

//        print_r($kindArray);
//echo $userId;
        // 1) Delete하고 insert 하기
        $deleteQuery = "DELETE FROM user_category
WHERE user_id = ?;";
        $st = $pdo->prepare($deleteQuery);
        $st->execute([$userId]);

//        echo $deleteQuery;

//        echo gettype($kindArray);
        foreach ($kindArray as $key => $value) {
            $wordQuery = "select id from sub_category
where name = '". $value. "';";
//            echo $wordQuery;
//            echo $value;
            $st = $pdo->prepare($wordQuery);
            $st->execute();
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $videoId = $st->fetch();

//            print_r($videoId);
//             echo $videoId['id'];
//            $videoId['id'];


            $insertQuery = "insert into user_category (user_id, sub_category_id) VALUES (?, ?);";
            $st = $pdo->prepare($insertQuery);
            $st->execute([$userId, $videoId['id']]);

//            echo $videoId['id'];
        }

        $query = "select id videoId,
       url, title, publisher, content,
       IF(h.status is null, 'N', status) heart
       from video
LEFT JOIN heart h on video.id = h.video_id and user_id = ? ";

        if ($keyword == '') {

        } else {
            $query = $query . "JOIN (select video_id videoId from keyword
where word in " . $keyword . ") WORD ON WORD.videoId = video.id";
//            print($keyword) ;
        }


        $st = $pdo->prepare($query);
        $st->execute([$userId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();


        foreach ($res as $key => $value) {

            $res[$key]['keywords'] = getVideoKeywords($res[$key]['videoId']);
            $res[$key]['thumbnail'] = "http://img.youtube.com/vi/" . $res[$key]['url'] . "/0.jpg";
        }


        $pdo->commit();

    } catch (\Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
            return null;
        }
        throw $e;
    }


    $st = null;
    $pdo = null;

    return $res;
}

function isValidUser($email, $pw)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM user WHERE email= ? AND password = ?) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$email, $pw]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);

}

function getCategoryName($categoryId)
{

    $pdo = pdoSqlConnect();
    $query = "select name from category where id =?;";

    $st = $pdo->prepare($query);
    $st->execute([$categoryId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetch();

    $st = null;
    $pdo = null;
//echo $res['name'];
    return $res;

}

function getSub($categoryId, $userId)
{

    $pdo = pdoSqlConnect();
    $query = "select  name
from user_category
         LEFT JOIN sub_category sc on user_category.sub_category_id = sc.id
where category_id =? and user_id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$categoryId, $userId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;

}

function hasKeywords($userId)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM user_category WHERE user_id= ?) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);

}

