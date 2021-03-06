<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
         * API No. 1-1
         * API Name : 로그인
         * 마지막 수정 날짜 : 20.05.23
         */
        case "postUser":
            http_response_code(200);

            $email = $req->email;
            $pw = $req->pw;

            if (!isset($email) or !isset($pw)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "이메일 주소 또는 비밀번호를 다시 확인하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if (!isValidUser($email, $pw)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "이메일 주소 또는 비밀번호를 다시 확인하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $jwt = getJWToken($email, $pw, JWT_SECRET_KEY);

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->email;
            // $userId = getUserId($userEmail);

            $res->result->jwt = $jwt;
            // $res->result->userId = $userId;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "로그인 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 2-1
         * API Name : 추천 리스트
         * 마지막 수정 날짜 : 20.05.23
         */
        case "getPicks":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (isset($jwt)) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
            }

            $categoryArr = [1, 2, 3];
            $real = Array();
            foreach ($categoryArr as $key => $value) {
                $temp = Array();
                $temp2 = Array();

                $categoryId = 1;
                $temp = getPicks($value);
                $temp2['picks'] = getSpecificPicks($value);

                $real[$key] = array_merge($temp, $temp2);
            }


            $res->result = $real;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "추천 리스트";


            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getPick":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $pickId = $vars['pick-id'];


            if (isset($jwt)) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

                $userEmail = $data->email;

                $userId = getUserId($userEmail);


            } else {
                $userId = 0;
            }

            if (!isExistPick($pickId)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "해당 Pick 식별자가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            $temp = Array();
            $temp2 = Array();
            $temp3 = Array();

            $temp = getSubImgUrl($pickId);
            $temp2['videos'] = getVideos($userId, $pickId);


            $real = array_merge($temp, $temp2, $temp3);

            $res->result = $real;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "영상 리스트(추천)";


            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "getLikes":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (isset($jwt)) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

                $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $userEmail = $data->email;
                $userId = getUserId($userEmail);
            } else {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "아직 좋아요를 누른 영상이 없어요!";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            $category = $_GET['category'];
            if (!isset($category)) {

                $category = '';
            } else {

                $realKind = "(";

                $temp = str_replace(" ", "", $category);
                $kindArray = explode(',', $temp);

                $kindValue = array('홈카페', '홈쿠킹', '홈트레이닝');
                foreach ($kindArray as $key => $value) {

                    $validPrice = $kindArray[$key];
                    if (!in_array($validPrice, $kindValue)) {
                        $res->isSuccess = FALSE;
                        $res->code = 400;
                        $res->message = "Query Params를 확인하세요.(category 값은 홈카페, 홈쿠킹, 홈트레이닝 입니다.)";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    $realKind = $realKind . '\'' . $kindArray[$key] . '\'';
                    if ($value === end($kindArray)) {
                        $realKind = $realKind . ')';
                    } else {
                        $realKind = $realKind . ',';
                    }
                }

                $category = ' and CATEGORY.name in ' . $realKind;

//                echo $realKind;

            }


            $real['videos'] = getLikes($userId, $category);


            if ($real['videos'] == null) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "아직 좋아요를 누른 영상이 없어요!";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            $res->result = $real;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "영상 리스트(좋아요)";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "getPreference":

            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (isset($jwt)) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

                $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $userEmail = $data->email;
                $userId = getUserId($userEmail);
            } else {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "취향을 등록하면 영상을 추천해드려요!";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            $keyword = $_GET['keyword'];
            $originalKeyword = $keyword;
            if (!isset($keyword)) {

                $keyword = '';
            } else {

                $realKind = "(";

                $temp = str_replace(" ", "", $keyword);
                $kindArray = explode(',', $temp);

//                 $kindValue = array('쿠키', '빵', '커피');
                $kindValue = getSubCategory();
//                print_r($kindValue);

                $keywordArray = implode(', ', $kindValue);

                foreach ($kindArray as $key => $value) {

                    $validPrice = $kindArray[$key];
                    if (!in_array($validPrice, $kindValue)) {
                        $res->isSuccess = FALSE;
                        $res->code = 400;
                        $res->message = "Query Params를 확인하세요.(keyword 값은 " . $keywordArray . "입니다.)";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    $realKind = $realKind . '\'' . $kindArray[$key] . '\'';
                    if ($value === end($kindArray)) {
                        $realKind = $realKind . ')';
                    } else {
                        $realKind = $realKind . ',';
                    }
                }

                $keyword = $realKind;

//                echo $realKind;

            }


            $real['videos'] = getPreference($userId, $keyword, $kindArray);


            if ($real['videos'] == null) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "관련 영상이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            $res->result = $real;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "영상 리스트(내취향)";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "getVideo":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $videoId = $vars['video-id'];

            if (isset($jwt)) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

                $userEmail = $data->email;

                $userId = getUserId($userEmail);


            } else {
                $userId = 0;
            }

            if (!isExistVideo($videoId)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "해당 Video 식별자가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $temp = Array();
            $temp2 = Array();

            $temp = getVideo($userId, $videoId);
            $temp2['keywords'] = getVideoKeywords($videoId);

            $real = array_merge($temp, $temp2);

            $res->result = $real;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "video 상세 조회";


            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "postVideoLike":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "로그인을 해야 이용 가능한 서비스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->email;

            $userId = getUserId($userEmail);

            $videoId = $vars['video-id'];


            // 있는지 먼저 검사하고 없으면 insert, 있으면 update


            $status = '';
            if (!isExistLike($userId, $videoId)) {
                $status = 'insert';
                postHeart($userId, $videoId, $status);

                $res->result = userHeartStatus($userId, $videoId);
                $res->isSuccess = TRUE;
                $res->code = 200;
                $res->message = "Video 좋아요 - 처음 누름";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            } else {
                $status = 'update';
                postHeart($userId, $videoId, $status);

                $res->result = userHeartStatus($userId, $videoId);
                $res->isSuccess = TRUE;
                $res->code = 200;
                $res->message = "Video 좋아요(추가/삭제)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

        case "getCategories":

        case "getKeywords":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "로그인을 해야 이용 가능한 서비스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userEmail = $data->email;
            $userId = getUserId($userEmail);


        if (!hasKeywords($userId)) {
            $res->isSuccess = FALSE;
            $res->code = 400;
            $res->message = "취향을 등록해주세요.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;
        }


            $categoryArr = [1, 2, 3];
            $real = Array();
            foreach ($categoryArr as $key => $value) {
                $temp = Array();
                $temp2 = Array();

                $temp = getCategoryName($value);
                $temp2['sub'] = getSub($value, $userId);

                $real[$key] = array_merge($temp, $temp2);
            }


            $res->result = $real;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "추천 리스트";


//            $real['videos'] = getPreference($userId, $keyword, $kindArray);
//
//
//            if ($real['videos'] == null) {
//                $res->isSuccess = FALSE;
//                $res->code = 400;
//                $res->message = "취향을 등록하면 영상을 추천해드려요!";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                return;
//            }
//
//
//            $res->result = $real;
//            $res->isSuccess = TRUE;
//            $res->code = 200;
//            $res->message = "영상 리스트(내취향)";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
