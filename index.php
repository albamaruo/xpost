<?php
function h($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

$accounts = [
    [
        'auth_token' => '',//以降変更必須項目詳しくは説明参照
        'ct0' => '',
        'bearer' => ''
    ],
    [
        'auth_token' => '',
        'ct0' => '',
        'bearer' => ''
    ],
     //以降も同様にアカウントを追加できます

];
$logs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['tweet_text'] ?? '';
    $reply_id = $_POST['reply_id'] ?? '';
    $totalTweets = 0;
    $maxTweets = 200;//最大ツイート数を指定するけど１２０秒以上処理が続くので変えなくてもいいけどこれより少なくするとログがちゃんと表示される

    while ($totalTweets < $maxTweets) {
        foreach ($accounts as $i => $acc) {
            if ($totalTweets >= $maxTweets) break;

            $bearerToken = $acc['bearer'];
            $csrfToken = $acc['ct0'];
            $cookies = 'auth_token=' . $acc['auth_token'] . '; ct0=' . $csrfToken . ';';

            $rand = rand(1000, 9999);//ツイートの重複回避の乱数
            $tweet_text = $text . ' ' . $rand;

            $variables = [
                'tweet_text' => $tweet_text,
                'dark_request' => false,
                'media' => (object)[],
                'richtext' => false,
                'semantic_annotation_ids' => [],
            ];
            if ($reply_id !== '') {
                $variables['reply'] = [
                    'in_reply_to_tweet_id' => $reply_id
                ];
            }

            $data = [
                'variables' => $variables,
                'features' => [
                    'tweetypie_unmention_optimization_enabled' => true,
                    'vibe_api_enabled' => true,
                    'responsive_web_edit_tweet_api_enabled' => true,
                    'graphql_is_translatable_rweb_tweet_enabled' => true,
                    'view_counts_everywhere_enabled' => true,
                    'profile_label_improvements_pcf_label_in_post_enabled' => false,
                    'communities_web_enable_tweet_community_results_fetch' => false,
                    'standardized_nudges_misinfo' => false,
                    'tweet_awards_web_tipping_enabled' => false,
                    'rweb_xchat_enabled' => false,
                    'responsive_web_grok_community_note_auto_translation_is_enabled' => false,
                    'responsive_web_graphql_timeline_navigation_enabled' => false,
                    'responsive_web_grok_analyze_post_followups_enabled' => false,
                    'longform_notetweets_inline_media_enabled' => false,
                    'responsive_web_grok_image_annotation_enabled' => false,
                    'rweb_tipjar_consumption_enabled' => false,
                    'c9s_tweet_anatomy_moderator_badge_enabled' => false,
                    'tweet_with_visibility_results_prefer_gql_limited_actions_policy_enabled' => false,
                    'responsive_web_twitter_article_tweet_consumption_enabled' => false,
                    'responsive_web_grok_imagine_annotation_enabled' => false,
                    'responsive_web_grok_share_attachment_enabled' => false,
                    'verified_phone_label_enabled' => false,
                    'creator_subscriptions_quote_tweet_preview_enabled' => false,
                    'responsive_web_grok_analyze_button_fetch_trends_enabled' => false,
                    'responsive_web_grok_analysis_button_from_backend' => false,
                    'graphql_is_translatable_rweb_tweet_is_translatable_enabled' => false,
                    'articles_preview_enabled' => false,
                    'longform_notetweets_rich_text_read_enabled' => false,
                    'view_counts_everywhere_api_enabled' => false,
                    'longform_notetweets_consumption_enabled' => false,
                    'premium_content_api_read_enabled' => false,
                    'freedom_of_speech_not_reach_fetch_enabled' => false,
                    'payments_enabled' => false,
                    'responsive_web_enhance_cards_enabled' => false,
                    'responsive_web_grok_show_grok_translated_post' => false,
                    'responsive_web_jetfuel_frame' => false,
                    'responsive_web_graphql_skip_user_profile_image_extensions_enabled' => false,
                ],
                'queryId' => '<tweetをpostしたときの通信を見てコードを入れる、詳しくは説明から>'//変更必須項目
            ];
            $jsonData = json_encode($data);

            $ch = curl_init('https://x.com/i/api/graphql/<tweetをpostしたときの通信を見てコードを入れる、詳しくは説明から>/CreateTweet'); //変更必須項目
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'authorization: ' . $bearerToken,
                'x-csrf-token: ' . $csrfToken,
                'content-type: application/json',
                'cookie: ' . $cookies,
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0',//ユーザーエージェント
                'referer: https://x.com/compose/post'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $now = date('H:i:s');
            if ($httpCode == 5) {
                $logs[] = "<div class='mb-1'><span class='text-gray-400'>[{$now}]</span> <span class='text-green-400'>アカウント" . ($i+1) . ":</span> <span class='text-green-500'>ツイート成功！</span> " . h($response) . "</div>";
            } else {
                $logs[] = "<div class='mb-1'><span class='text-gray-400'>[{$now}]</span> <span class='text-green-400'>アカウント" . ($i+1) . ":</span> <span class='text-red-500'>ツイート失敗:</span> " . h($response) . "</div>";
            }

            $totalTweets++;
           
            if ($totalTweets < $maxTweets) {
                sleep(rand(0.5,1));　//あんま関係ないけど念のためツイートする間隔の指定（開発当初はレート制限回避をどうするか試行錯誤してたため）
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Xツイートツール</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 min-h-screen flex flex-col items-center justify-start py-10">
    <div class="w-full max-w-xl">
        <form id="tweetForm" method="post" class="bg-gray-800 p-6 rounded-lg shadow-lg mb-6">
            <label class="block text-green-400 font-mono mb-2">ツイート内容:</label>
            <input type="text" name="tweet_text" value="それなｗ" required class="w-full mb-4 px-3 py-2 rounded bg-gray-700 text-green-300 font-mono focus:outline-none focus:ring-2 focus:ring-green-400">
            <label class="block text-green-400 font-mono mb-2">返信先ツイートID（空欄なら通常投稿）:</label>
            <input type="text" name="reply_id" value="" class="w-full mb-4 px-3 py-2 rounded bg-gray-700 text-green-300 font-mono focus:outline-none focus:ring-2 focus:ring-green-400">
            <button type="submit" class="w-full py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded transition">ツイート開始</button>
        </form>
        <div class="bg-black rounded-lg p-6 shadow-lg font-mono text-green-400 text-sm" style="min-height:300px;max-height:400px;overflow-y:auto;" id="cmdLog">
            <?php
            if (!empty($logs)) {
                foreach ($logs as $log) echo $log;
            } else {
                echo "<span class='text-gray-500'>[INFO] ツイートログがここに表示されます</span>";
            }
            ?>
        </div>
    </div>
</body>
</html>
