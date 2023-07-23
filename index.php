<?php
$email = 'email@email.com';
$password = 'password';

function getSignIn($email, $password){
    $url = 'https://api.2performant.com/users/sign_in.json';

    $data = array(
        'user' => array(
            'email' => $email,
            'password' => $password
        )
    );

    $jsonData = json_encode($data);
    $headers = [];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);

    $response = curl_exec($ch);

    $headersSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headersSize);

    curl_close($ch);

    $headersLines = explode("\r\n", $headers);

    $signParameters = [];
    foreach ($headersLines as $line) {
        if (strpos($line, 'access-token') !== false) {
            $signParameters['access-token'] = trim(str_replace('access-token:', '', $line));
        }
        if (strpos($line, 'client') !== false) {
            $signParameters['client'] = trim(str_replace('client:', '', $line));
        }
        if (strpos($line, 'uid') !== false) {
            $signParameters['uid'] = trim(str_replace('uid:', '', $line));
        }
    }
    return $signParameters;

}

function getData($signParameters, $url, $queryParams = null) {
    $jsonParameters = json_encode($signParameters);

    if ($queryParams) {
        $url = $url . '?' . http_build_query($queryParams);
    }

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_GET, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonParameters);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        return "cURL Error: " . $error;
    }
    curl_close($ch);

    return $response;
}


$signParameters = getSignIn($email, $password);
$queryParamsPromotions = array(
    'page' => 1,
    'perpage'=> 20
);
$urlPromotions = 'https://api.2performant.com/affiliate/advertiser_promotions';

?>
<html>
<head>
    <title>2Performant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
<div class="container px-4 py-5" id="custom-cards">
    <h2 class="pb-2 border-bottom">2 Performant Last promotions</h2>
    <div class="row row-cols-1 row-cols-lg-3 align-items-stretch g-4 py-5">
        <?php for($j=1; $j<=1; $j++){
            $promotions = getData($signParameters, $urlPromotions, $queryParamsPromotions);
            $promotions = json_decode($promotions);
            for($i=0; $i<=20; $i++){
        ?>

      <div class="col">
        <div class="card card-cover h-100 overflow-hidden text-bg-white rounded-4 shadow-lg" style="background-image: url('unsplash-photo-1.jpg');">
          <div class="d-flex flex-column h-100 p-5 pb-3 text-black text-shadow-1">
            <h3 class="pt-5 mt-5 mb-4 display-6 lh-1 fw-bold"><?php echo $promotions->advertiser_promotions[$i]->name;?></h3>
            <li class="list-unstyled">
            <i class="bi bi-shield-check"></i> ID: <?php echo $promotions->advertiser_promotions[$i]->id;?>
            </li>
            <li class="list-unstyled">
            <i class="bi bi-calendar-event"></i><strong> Start date: </strong><?php $date = new DateTime($promotions->advertiser_promotions[$i]->promotion_start); echo $date->format("Y-m-d");?>
            <br>
            <i class="bi bi-calendar-event"></i><strong> End date: </strong><?php $date = new DateTime($promotions->advertiser_promotions[$i]->promotion_end); echo $date->format("Y-m-d");?>

            </li>
            <li class="list-unstyled">
            <i class="bi bi-globe"> </i>Website: <?php echo $promotions->advertiser_promotions[$i]->campaign_name;?>
            </li>
            <br><br>
            <ul class="d-flex list-unstyled mt-auto">
              <li class="me-auto">
                <img src="<?php echo $promotions->advertiser_promotions[$i]->campaign_logo;?>" alt="logo" height=50px>
                </li>
                <br><br>    
              <li class="d-flex align-items-center me-3">
                <a class="btn btn-outline-dark" href="<?php echo $promotions->advertiser_promotions[$i]->landing_page_link;?>" target="_blank">See the offer</a>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <?php }$queryParamsPromotions['page'] = $j;}?>  
      </div>
    </div>
  </div>
</body>
</html>