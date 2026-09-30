<?php
if (!defined('ABSPATH')) exit;
class BluTutor_Students_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_post_blututor_save_student', [$this, 'save']);
        add_action('admin_post_blututor_delete_student', [$this, 'delete']);
    }
    public function menu() {
        add_menu_page('BluTutor Students','BluTutor','manage_options','blututor-students',[$this,'page'],'dashicons-welcome-learn-more',26);
        add_submenu_page('blututor-students','Students','Students','manage_options','blututor-students',[$this,'page']);
    }
    public function page() {
        if (!current_user_can('manage_options')) return;
        global $wpdb; $table=blututor_core_table('students');
        $edit_id=isset($_GET['edit'])?absint($_GET['edit']):0;
        $edit=$edit_id?$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d",$edit_id)):null;
        $rows=$wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
        ?>
        <div class="wrap"><h1 class="wp-heading-inline">BluTutor Students</h1><a class="page-title-action" href="<?php echo esc_url(admin_url('admin.php?page=blututor-students')); ?>">Add Student</a><hr class="wp-header-end">
        <?php if(isset($_GET['saved'])): ?><div class="notice notice-success is-dismissible"><p>Student saved.</p></div><?php endif; ?>
        <div class="card" style="max-width:900px;padding:20px;margin-top:20px"><h2><?php echo $edit?'Edit Student':'Add Student'; ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="blututor_save_student"><input type="hidden" name="id" value="<?php echo esc_attr($edit->id??0); ?>"><?php wp_nonce_field('blututor_save_student'); ?>
        <table class="form-table"><tr><th>Name</th><td><input required class="regular-text" name="name" value="<?php echo esc_attr($edit->name??''); ?>"></td></tr><tr><th>Email</th><td><input type="email" required class="regular-text" name="email" value="<?php echo esc_attr($edit->email??''); ?>"></td></tr><tr><th>Phone</th><td><input class="regular-text" name="phone" value="<?php echo esc_attr($edit->phone??''); ?>"></td></tr><tr><th>Grade Level</th><td><input class="regular-text" name="grade_level" value="<?php echo esc_attr($edit->grade_level??''); ?>"></td></tr><tr><th>Subject</th><td><input class="regular-text" name="subject" value="<?php echo esc_attr($edit->subject??''); ?>"></td></tr><tr><th>Status</th><td><select name="status"><?php foreach(['New','Active','Matched','Inactive'] as $s): ?><option <?php selected($edit->status??'New',$s); ?>><?php echo esc_html($s); ?></option><?php endforeach; ?></select></td></tr><tr><th>Notes</th><td><textarea class="large-text" rows="4" name="notes"><?php echo esc_textarea($edit->notes??''); ?></textarea></td></tr></table>
        <?php submit_button($edit?'Update Student':'Add Student'); ?></form></div>
        <h2 style="margin-top:35px">Students</h2><table class="widefat striped"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Grade</th><th>Subject</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?php echo (int)$r->id; ?></td><td><?php echo esc_html($r->name); ?></td><td><?php echo esc_html($r->email); ?></td><td><?php echo esc_html($r->grade_level); ?></td><td><?php echo esc_html($r->subject); ?></td><td><?php echo esc_html($r->status); ?></td><td><?php echo esc_html($r->created_at); ?></td><td><a href="<?php echo esc_url(admin_url('admin.php?page=blututor-students&edit='.$r->id)); ?>">Edit</a> | <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=blututor_delete_student&id='.$r->id),'blututor_delete_student_'.$r->id)); ?>" onclick="return confirm('Delete this student and their matches?');">Delete</a> | <a href="<?php echo esc_url(admin_url('admin.php?page=blututor-matches&student_id='.$r->id)); ?>">Matches</a></td></tr><?php endforeach; ?></tbody></table></div><?php
    }
    public function save(){
        if(!current_user_can('manage_options')) wp_die('Unauthorized'); check_admin_referer('blututor_save_student'); global $wpdb; $id=absint($_POST['id']??0); $data=['name'=>sanitize_text_field(wp_unslash($_POST['name']??'')),'email'=>sanitize_email(wp_unslash($_POST['email']??'')),'phone'=>sanitize_text_field(wp_unslash($_POST['phone']??'')),'grade_level'=>sanitize_text_field(wp_unslash($_POST['grade_level']??'')),'subject'=>sanitize_text_field(wp_unslash($_POST['subject']??'')),'status'=>sanitize_text_field(wp_unslash($_POST['status']??'New')),'notes'=>sanitize_textarea_field(wp_unslash($_POST['notes']??'')),'updated_at'=>current_time('mysql')];
        if($id) $wpdb->update(blututor_core_table('students'),$data,['id'=>$id]); else {$data['created_at']=current_time('mysql');$wpdb->insert(blututor_core_table('students'),$data);$id=$wpdb->insert_id;} blututor_core_create_matches($id); wp_safe_redirect(admin_url('admin.php?page=blututor-students&saved=1')); exit;
    }
    public function delete(){ $id=absint($_GET['id']??0); if(!current_user_can('manage_options'))wp_die('Unauthorized'); check_admin_referer('blututor_delete_student_'.$id); global $wpdb; $wpdb->delete(blututor_core_table('matches'),['student_id'=>$id]);$wpdb->delete(blututor_core_table('students'),['id'=>$id]);wp_safe_redirect(admin_url('admin.php?page=blututor-students'));exit; }
}