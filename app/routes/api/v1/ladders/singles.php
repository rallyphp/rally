<?php
$app->get('/top10', function () use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $db = db();

    $query = <<<SQL
SELECT id,
  first_name,
  last_name,
  ROUND(rating - 3 * uncertainty, 3) rating,
  email
FROM players
ORDER BY rating - 3 * uncertainty DESC
LIMIT 10
SQL;

    $st = $db->query($query);
    $players = [];

    foreach ($st as $row) {
        $players[] = [
            'id' => $encoder->encode($row['id']),
            'firstName' => $row['first_name'],
            'lastName' => $row['last_name'],
            'rating' => $row['rating'],
            'avatar' => sprintf(
                'https://secure.gravatar.com/avatar/%s?s=40&d=mm',
                md5($row['email'])
            )
        ];
    }

    $response = $app->response();
    $response->headers->set('Content-Type', 'application/json');
    $response->setBody(json_encode($players));
});
