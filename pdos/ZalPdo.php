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

function getSubImgUrl($pickId){
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

function getVideos($userId, $pickId){
    $pdo = pdoSqlConnect();
    $query = "select id videoId,
       url, title, publisher,
       IF(h.status is null, 'N', status) heart
       from video
LEFT JOIN heart h on video.id = h.video_id and user_id = ?
where pick_id=?;
";

    $st = $pdo->prepare($query);
    $st->execute([$userId, $pickId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

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
////READ
//function testDetail($testNo)
//{
//    $pdo = pdoSqlConnect();
//    $query = "SELECT * FROM Test WHERE no = ?;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$testNo]);
//    //    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0];
//}
//
//
//function testPost($name)
//{
//    $pdo = pdoSqlConnect();
//    $query = "INSERT INTO Test (name) VALUES (?);";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$name]);
//
//    $st = null;
//    $pdo = null;
//
//}


function isValidUser($email, $pw){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM user WHERE email= ? AND password = ?) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$email, $pw]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}


// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
