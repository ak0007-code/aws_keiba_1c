<?php
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
define( 'DB_NAME', 'aws_and_infra' );

/** MySQL データベースのユーザー名 */
define( 'DB_USER', 'aws_and_infra' );

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
define( 'AUTH_KEY',         'y<[qW 75b; 7EaN7vre|Fj^K==&K&v2E7hj3Bu}95v:Lq:>c>+_QSac*A=p]x=#>' );
define( 'SECURE_AUTH_KEY',  '&Whi7><4h_lZ7-8XpS`~*amT fM{s]/2Cmy7=O*+/pSJ%yPF@`)j~_rivJO`e fL' );
define( 'LOGGED_IN_KEY',    '(Ns_D7&{+5m`nt<+K81R.yoH^giF?Vg z__u, I]Z%6?j!=C-N17b1W`Kyb/C#NH' );
define( 'NONCE_KEY',        'o)z0=iY(C?`XtL >0);7Q}N#z2x?Yl`MdS/OM3fZhj73,>LQ}DvCv<*2`G1cO^Mb' );
define( 'AUTH_SALT',        'Ifa9!YB>R<aEPC}.]agqZ%%mcc|@Cn<gPJVU#N~fXG!p,.Y:lAX*l :n#p59.C_W' );
define( 'SECURE_AUTH_SALT', ' g.mMwjR#~_x](*)aVl$-m&L**2$G I%V,3B1O=:G1i9[[%dZ)Q{U5EL~a[/G(HC' );
define( 'LOGGED_IN_SALT',   '|g/hyD4^N>-+U(,UZ>;73.oiJ&@$UB7DQaMz<F)c18N,D:i|xGXz1;qx5)Y&(W(Z' );
define( 'NONCE_SALT',       'm2!N)Zst^[2N-9/UmmYhBC$puYz57%(PC*p;}c!p*FyHTT<Q?Uf/0~Vblg;DP]XE' );

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

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
