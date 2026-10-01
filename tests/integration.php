<?php
if ( 'yes' !== getenv( 'PAT_TEST_DISPOSABLE' ) ) { fwrite( STDERR, 'Use an isolated disposable test site.\n' ); exit(1); }
require rtrim( getenv( 'PAT_TEST_ROOT' ), '/' ) . '/wp-load.php';
require_once ABSPATH.'wp-admin/includes/plugin.php';
require_once ABSPATH.'wp-admin/includes/file.php';
set_exception_handler(function($e){fwrite(STDERR,$e->getMessage()."\n");exit(1);});
$checks=0;
function verify($ok,$name){global $checks;if(!$ok)throw new RuntimeException($name);echo "PASS $name\n";$checks++;}
wp_set_current_user(1);
add_filter('pre_determine_locale',fn()=> 'de_DE');
$repo='https://github.com/deckerweb/post-author-taxonomy';
$key='ddw_ghru_'.substr(md5($repo),0,24);
$release=['version'=>'1.3.1','package'=>$repo.'/releases/download/v1.3.1/post-author-taxonomy.zip','notes'=>'Test notes','published'=>'2026-10-01T12:00:00Z'];
set_site_transient($key,$release,60);
$adapter=new \Deckerweb\PostAuthorTaxonomy\GitHubUpdates();
$art=$adapter->artwork();
verify(str_contains($art['banners']['high'],'banner-de-1544x500.png'),'Updater uses local German banner');
$updater=new \Deckerweb\GitHubReleaseUpdater\V2\Updater(PAT_PLUGIN_FILE,$repo,'Post Author Taxonomy','Description',$art);
$headers=get_plugin_data(PAT_PLUGIN_FILE,false,false);
$update=$updater->update(false,$headers,'post-author-taxonomy/post-author-taxonomy.php',['de_DE']);
verify(is_array($update)&&'1.3.1'===$update['version'],'Updater offers newer stable release');
verify(false===$updater->update(false,$headers,'other/other.php',[]),'Updater leaves other plugins untouched');
$headers['Version']='9.0.0';
verify(false===$updater->update(false,$headers,'post-author-taxonomy/post-author-taxonomy.php',[]),'Updater does not downgrade');
$info=$updater->information(false,'plugin_information',(object)['slug'=>'post-author-taxonomy']);
verify(is_object($info)&&isset($info->banners['high']),'Plugin details includes artwork');
$limits=$adapter->request_limits([], 'https://api.github.com/repos/deckerweb/post-author-taxonomy/releases/latest');
verify($limits['sslverify']&&0===$limits['redirection']&&524288===$limits['limit_response_size'],'Scoped metadata request limits');
verify([]===$adapter->request_limits([], 'https://example.test'),'Other HTTP requests unchanged');
WP_Filesystem();
$fixture=ABSPATH.'pat-update-candidate';wp_mkdir_p($fixture);
$extra=['plugin'=>'post-author-taxonomy/post-author-taxonomy.php','type'=>'plugin','action'=>'update'];
function candidate($version,$name='Post Author Taxonomy',$php='8.0'){
 global $fixture;
 file_put_contents($fixture.'/post-author-taxonomy.php',"<?php\n/*\nPlugin Name: $name\nVersion: $version\nUpdate URI: https://github.com/deckerweb/post-author-taxonomy\nRequires PHP: $php\nRequires at least: 6.7\n*/\n");
}
set_site_transient('update_plugins',(object)['response'=>['post-author-taxonomy/post-author-taxonomy.php'=>(object)['new_version'=>'1.3.1']]]);
candidate('1.3.1');verify($fixture===$adapter->validate_source($fixture,null,null,$extra),'Matching candidate accepted');
candidate('1.3.1','Wrong Plugin');verify(is_wp_error($adapter->validate_source($fixture,null,null,$extra)),'Wrong package identity rejected');
candidate('1.3.2');verify(is_wp_error($adapter->validate_source($fixture,null,null,$extra)),'Unexpected version rejected');
candidate('1.3.1','Post Author Taxonomy','99.0');verify(is_wp_error($adapter->validate_source($fixture,null,null,$extra)),'Unsupported requirements rejected');
verify('/untouched'===$adapter->validate_source('/untouched',null,null,['plugin'=>'other/other.php']),'Other upgrade source untouched');
$term=get_term_by('slug','alice-bob','pat-author');
$photo = (int) getenv('PAT_TEST_PHOTO_ID');
update_term_meta($term->term_id,'pat_photo_id',$photo);
verify(str_contains(do_shortcode('[pat-author-box slug="alice-bob"]'),'pat-author-box__photo'),'Stored local photo rendered');
verify(!str_contains(do_shortcode('[pat-author-box slug="alice-bob" photo="no"]'),'pat-author-box__photo'),'Photo can be hidden');
$rest=new WP_REST_Request('POST','/wp/v2/pat-author/'.$term->term_id);
$rest->set_param('meta',['pat_website'=>'https://rest.example.test']);
wp_set_current_user(0);
$response=rest_do_request($rest);
verify($response->get_status()>=400,'Anonymous REST profile update rejected');
wp_set_current_user(1);
$response=rest_do_request($rest);
verify(200===$response->get_status()&&'https://rest.example.test'===get_term_meta($term->term_id,'pat_website',true),'Authorized REST profile update accepted');
verify('div'===substr(do_shortcode('[pat-author-box slug="alice-bob" wrapper="p"]'),1,3),'Box wrapper rejects invalid paragraph nesting');
delete_site_transient($key);delete_site_transient('update_plugins');

echo "Completed $checks integration checks\n";
