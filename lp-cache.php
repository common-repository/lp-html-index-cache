<?php
/**
 * Plugin Name:       LP HTML Index Cache
 * Description:       LP Cache will generate cache static html eny website to fast website. Free and no need CDN on your website Because this tool just make only index.html
 * Version:           1.0.0
 * Author:            Le.VanPhu
 * Author URI:        http://levanphu.info
 * Text Domain:       levanphu
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Plugin URI: http://wordpress.org/plugins/lp-cache/
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
/*
 * Plugin constants
 */
if(!defined('LP_CACHE_URL'))
	define('LP_CACHE_URL', plugin_dir_url( __FILE__ ));
if(!defined('LP_CACHE_PATH'))
	define('LP_CACHE_PATH', plugin_dir_path( __FILE__ ) . 'cache/');
 
/*
 * Main class
 */
/**
 * Class LPCache
 *
 * This class creates the option page and add the web app script
 */
class LPCache
{
 
    /**
     * LPCache constructor.
     *
     * The main plugin actions registered for WordPress
     */
    public function __construct()
    {
    	$this->init();
    }
 	public function init()
 	{
 		// We need some CSS to position the paragraph
		function lp_cache_css()
		{
			echo "
				<style>
				picture > img {
					margin-top: 23px;
					width: calc(100% - 40px);
					border: 1px solid #f3f4f5;
				}
				.lp-cache-panel-column-container {
					display: grid;
					-ms-grid-columns: 36% 32% 32%;
					grid-template-columns: 36% 32% 32%;
				}
				.lp-cache-panel-column {
					display: grid;
					-ms-grid-rows: auto 100px;
					grid-template-rows: auto 100px;
					width: auto;
					flex-direction: column;
					justify-content: space-between;
				}
			</style>
			";
		}

		add_action( 'admin_head', 'lp_cache_css' );
 		add_action( 'init', 'bootstrap' );
		function bootstrap()
		{
			$current_url = (isset($_SERVER['HTTPS']) && is_admin() == false && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$url_path = parse_url( $current_url, PHP_URL_PATH );
			$cacheName = pathinfo( $url_path, PATHINFO_BASENAME );
			$fileCache = LP_CACHE_PATH . $cacheName . '.html';
			if (!is_dir(LP_CACHE_PATH))
			{
			    @mkdir(LP_CACHE_PATH);         
			}
			if (file_exists($fileCache) && empty($_GET['status']) && !has_search())
			{
			    $output = file_get_contents($fileCache);
            	$output = gzuncompress($output);
            	echo $output;
			    exit;
			} elseif(empty($_GET['status']))
			{
				require 'include/init.php';
				LPCacheLoading($current_url , $cacheName);
			}
		 }
 		add_action('admin_menu', 'my_admin_menu');
		function my_admin_menu()
		{
		  add_menu_page('LP Cache', 'LP Cache', 'manage_options', __FILE__, 'LPDashboard');
		}
		function LPCacheLoading($current_url, $cacheName)
		{
			if (!is_admin() && !is_wplogin() && !has_search())
			{
				$url =  $current_url . '?status=1';
				$homepage = function_exists('file_get_contents') ? file_get_contents($url) : wp_die("This function_exists is disabled");
				LPSaveCache($homepage, LP_CACHE_PATH, $cacheName);
			}
		}
		function is_wplogin()
		{
		    $ABSPATH_MY = str_replace(array('\\','/'), DIRECTORY_SEPARATOR, ABSPATH);
		    return ((in_array($ABSPATH_MY.'wp-login.php', get_included_files()) || in_array($ABSPATH_MY.'wp-register.php', get_included_files()) ) || (isset($_GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'wp-login.php') || $_SERVER['PHP_SELF']== '/wp-login.php');
		}
		function has_search()
		{
			return isset($_GET['s']) ? true : false;
		}
		function LPDashboard()
		{
			if (!current_user_can('manage_options'))
			{
			    wp_die( __('You do not have sufficient pilchards to access this page.')    );
			}
			?>
			<div class="wrap">
			   <h1>LP Cache</h1>
			   <div class="lp-cache-panel">
			      <div class="lp-cache-panel-content">
			      	<?php if (isset($_POST['lp_cache_clear']) && check_admin_referer('lp_cache_token'))
					{
						$clear = clearCache(LP_CACHE_PATH);
						if ($clear)
						{
							include 'include/message.php';
						}
					}?>
					<h2>Cảm ơn bạn đã sử dụng LP Cache</h2>
					<p class="about-description">LP được xây dựng dựa trên ý tưởng của một người bạn thân của tôi trong lần cùng đi ăn cuối năm. Hiện tại các Plugin về Cache đã có rất nhiều tuy nhiên không giải quyết được các vấn đề như: Thời gian phản hồi máy chủ, độ nén của tệp, giảm tải các yêu cầu ... Chính vì cách mà các plugin đó hoạt động đều phải thông qua quá trình xử lý của phí PHP và Apache hoặc Nginx để trả về kết quả mà không phản hồi 1 tệp tỉnh nào đó.</p>
					<hr>
			        <div class="lp-cache-panel-column-container">
			            <div class="lp-cache-panel-column lp-cache-panel-image-column">
			               <picture>
			                  <source srcset="about:blank" media="(max-width: 1024px)">
			                  <img src="<?=LP_CACHE_URL?>assets/img/php-cache-system.jpg" alt="Ảnh chụp mô tả chức năng của LP Cache">
			               </picture>
			            </div>
			            <div class="lp-cache-panel-column plugin-card-gutenberg">
			               	<div>
			                  <h3>Vận Hành</h3>
			                  <p>
			                     LP Cache sẽ lưu lại 1 tệp Cache (HTML) được nén tối đa. Sau khi khách hàng truy cập vào website của bạn LP Cache sẽ xử lý việc đọc file cache cố định đã nén trước đó mà không thông qua xử lý Render từ các Plugin hay Theme và các chức năng khác không được gọi.				
			                  </p>
			                  <p>Làm thời gian xử lý không tốn kém. Do chỉ đơn thuần là đọc 1 file HTML tỉnh đã được lưu trử trước đó</p>
			                  <p>Nếu truy cập vào 1 URL chưa được tạo trước đó LP Cache sẽ tự động tạo và xử lý nó.</p>
			               	</div>
			               	<div class="lp-cache-action">
				               	<form action="" method="post">
					  				<?=wp_nonce_field('lp_cache_token');?>
					  				<input type="hidden" value="true" name="lp_cache_clear" />
									<?=submit_button('Xóa Toàn Bộ Cache','button button-hero install-now');?>
								</form>
			               	</div>
			            </div>
			            <div class="lp-cache-panel-column plugin-card-classic-editor">
			               <div>
			                  <h3>Cảm ơn sự đóng góp</h3>
			                  <p> Ý tưởng: <b>Kiên Hữu</b><br>Email: huu.kien@webike.com.vn <br>
			                  </p>
			                  <p>Phát triển: <b>Lê Văn Phú</b><br>Email: le.vanphu@rivercrane.com.vn</p>
			                  <p>Đây là phiên bản Beta, chúng tôi sẽ cập nhật sớm nhất để đem lại nhiều hơn các tiện ích tối ưu website.</p>
			                  <p>Mọi ý kiến đóng gói gửi về địa chỉ <a href="mailto:vanphupc50@gmail.com">Lê Văn Phú</a> chúng tôi đang chờ đợi những ý tưởng của các bạn để biến nó thành sự thật.<a href="https://levanphu.info">Tìm hiểu thêm về LP Cache</a></p>
			               </div>
			            </div>
			        </div>
			      </div>
			   </div>
			</div>
		<?php }
		function clearCache($str)
		{
			//It it's a file.
			if (is_file($str)) {
			    //Attempt to delete it.
			    return unlink($str);
			}
			//If it's a directory.
			elseif (is_dir($str)) {
			    //Get a list of the files in this directory.
			    $scan = glob(rtrim($str,'/').'/*');
			    //Loop through the list of files.
			    foreach($scan as $index=>$path) {
			        //Call our recursive function.
			        clearCache($path);
			    }
			    //Remove the directory itself.
			    return true;
			}
		}
	}
}
 
/*
 * Starts our plugin class, easy!
 */
new LPCache();
?>