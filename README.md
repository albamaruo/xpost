phpでx.comのアカウント情報を使ってAPIの登録不要でツイートをできるようにしたツール。
使用方法（コード内の必須項目に入れる情報の取得方法）
auth_token...xで開発者モードを開きCOOKIEを確認、”application"にある"auth_token”
ct0...同様の場所に同じ名前（ct0）であります
bearer...開発者モードを起動して手動で適当なツイートをしてnetwork内の”create tweet”の部分に”bearer token”があるのでそこをコピペ
quertyid...上と同様でnetworkのcreate tweet内のrequests URLの
https://x.com/i/api/graphql/ここの部分/CreateTweet
