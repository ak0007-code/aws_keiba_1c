<?php
//Begin Really Simple SSL session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple SSL

/**
 * WordPress の基本設定
 *
 * このファイルは、インストール時に wp-config.php 作成ウィザードが利用します。
 * ウィザードを介さずにこのファイルを "wp-config.php" という名前でコピーして
 * 直接編集して値を入力してもかまいません。
 *
 * このファイルは、以下の設定を含みます。
 *
 * * MySQL 設定
 * * 秘密鍵
 * * データベーステーブル接頭辞
 * * ABSPATH
 *
 * @link https://ja.wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// 注意:
// Windows の "メモ帳" でこのファイルを編集しないでください !
// 問題なく使えるテキストエディタ
// (http://wpdocs.osdn.jp/%E7%94%A8%E8%AA%9E%E9%9B%86#.E3.83.86.E3.82.AD.E3.82.B9.E3.83.88.E3.82.A8.E3.83.87.E3.82.A3.E3.82.BF 参照)
// を使用し、必ず UTF-8 の BOM なし (UTF-8N) で保存してください。

// ** MySQL 設定 - この情報はホスティング先から入手してください。 ** //
/** WordPress のためのデータベース名 */
define( 'DB_NAME', 'aws_and_infra_1c' );

/** MySQL データベースのユーザー名 */
define( 'DB_USER', 'root' );

/** MySQL データベースのパスワード */
define( 'DB_PASSWORD', 'password' );

/** MySQL のホスト名 */
define( 'DB_HOST', 'aws-and-infra-web.cc4tusje8m3w.ap-northeast-1.rds.amazonaws.com' );

/** データベースのテーブルを作成する際のデータベースの文字セット */
define( 'DB_CHARSET', 'utf8mb4' );

/** データベースの照合順序 (ほとんどの場合変更する必要はありません) */
define( 'DB_COLLATE', '' );

/**#@+
 * 認証用ユニークキー
 *
 * それぞれを異なるユニーク (一意) な文字列に変更してください。
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org の秘密鍵サービス} で自動生成することもできます。
 * 後でいつでも変更して、既存のすべての cookie を無効にできます。これにより、すべてのユーザーを強制的に再ログインさせることになります。
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '1|:5^P@!y1oC@xL%1)DYLrVT{cIFNg<p=f%CC2smzjvUQLN%Pd|QI=fwhz2::/Ql' );
define( 'SECURE_AUTH_KEY',  '8Zm(Qk.Mg85ROr#jhQ5Jv;yuN0{l~`J%5IoYUOO{x44^+hvjQ5d!-.fnQq(zM!Xj' );
define( 'LOGGED_IN_KEY',    '?9A*4=HpV!@wr~d&Hq-o[gx|D16=>RE.&*,CF4>qRhcp|TBcWt$F:J-k`K`jbz x' );
define( 'NONCE_KEY',        'Y3Dd(=TAP+-9bzrok}IFG&?s%NH!q`O/m0u`.:uo=/WON~c[_<*fPpfS1k+jjeUu' );
define( 'AUTH_SALT',        '$7J6`k`=A7(-UXTdkSoW+nC:N)PUi*<CoAZD952=p.wsm]?#<?Tx0~1z_mBeRR1{' );
define( 'SECURE_AUTH_SALT', 'P62oR1*s>F(oFA1A^O;N!h1]*k%EZBK1/UKyE!>EJf3T7q&yov{%+)qM +Kjo$TL' );
define( 'LOGGED_IN_SALT',   'mrJ^[>DRFlh+@))6w!`s*_myeCDX`W^1CsmWA{bnBZqnYmg2YNuGXGq@Fj*g@lYQ' );
define( 'NONCE_SALT',       ':ib!~He~oku)tOgCiP2kj0Xu;y$`O%C ((o+ktWgL3PM1?pbfJ&II;Mwft#ugBAu' );

/**#@-*/

/**
 * WordPress データベーステーブルの接頭辞
 *
 * それぞれにユニーク (一意) な接頭辞を与えることで一つのデータベースに複数の WordPress を
 * インストールすることができます。半角英数字と下線のみを使用してください。
 */
$table_prefix = 'wp_';

/**
 * 開発者へ: WordPress デバッグモード
 *
 * この値を true にすると、開発中に注意 (notice) を表示します。
 * テーマおよびプラグインの開発者には、その開発環境においてこの WP_DEBUG を使用することを強く推奨します。
 *
 * その他のデバッグに利用できる定数についてはドキュメンテーションをご覧ください。
 *
 * @link https://ja.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* カスタム値は、この行と「編集が必要なのはここまでです」の行の間に追加してください。 */



/* 編集が必要なのはここまでです ! WordPress でのパブリッシングをお楽しみください。 */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/*** 追記 ***/
$_SERVER['HTTPS']='on';
define('FORCE_SSL_LOGIN', true);
define('FORCE_SSL_ADMIN', true);
/*** 追記 ***/

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
