<?php
if (!defined('ABSPATH')) exit;
class BluTutor_Tutors_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_post_blututor_save_tutor', [$this, 'save']);
        add_action('admin_post_blututor_delete_tutor', [$this, 'delete']);
    }
    public function menu() {
        add_submenu_page('blututor-students','Tutors','Tutors','manage_options','blututor-tutors',[$this,'page']);
    }
    public function page() {
        if (!current_user_can('manage_options')) return;
        global $wpdb; $table=blututor_core_table('tutors');
        $edit_id=isset($_GET['edit'])?absint($_GET['edit']):0;
        $edit=$edit_id?$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d",$edit_id)):null;
        $rows=$wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
        ?>
        <div class="wrap"><h1 class="wp-heading-inline">BluTutor Tutors</h1><a class="page-title-action" href="<?php echo esc_url(admin_url('admin.php?page=blututor-tutors')); ?>">Add Tutor</a><hr class="wp-header-end">
        <div class="card" style="max-width:900px;padding:20px;margin-top:20px"><h2><?php echo $edit?'Edit Tutor':'Add Tutor'; ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="blututor_save_tutor"><input type="hidden" name="id" value="<?php echo esc_attr($edit->id??0); ?>"><?php wp_nonce_field('blututor_save_tutor'); ?>
        <table class="form-table"><tr><th>Name</th><td><input required class="regular-text" name="name" value="<?php echo esc_attr($edit->name??''); ?>"></td></tr><tr><th>Email</th><td><input type="email" required class="regular-text" name="email" value="<?php echo esc_attr($edit->email??''); ?>"></td></tr><tr><th>Phone</th><td><input class="regular-text" name="phone" value="<?php echo esc_attr($edit->phone??''); ?>"></td></tr><tr><th>Subjects</th><td><textarea class="large-text" rows="3" name="subjects"><?php echo esc_textarea($edit->subjects??''); ?></textarea></td></tr><tr><th>Grade Levels</th><td><textarea class="large-text" rows="3" name="grade_levels"><?php echo esc_textarea($edit->grade_levels??''); ?></textarea></td></tr><tr><th>Status</th><td><select name="status"><?php foreach(['Pending','Approved','Active','Inactive'] as $s): ?><option <?php selected($edit->status??'Pending',$s); ?>><?php echo esc_html($s); ?></option><?php endforeach; ?></select></td></tr><tr><th>Availability</th><td><textarea class="large-text" rows="3" name="availability"><?php echo esc_textarea($edit->availability??''); ?></textarea></td></tr><tr><th>Notes</th><td><textarea class="large-text" rows="3" name="notes"><?php echo esc_textarea($edit->notes??''); ?></textarea></td></tr></table>
        <?php submit_button($edit?'Update Tutor':'Add Tutor'); ?></form></div>
        <h2 style="margin-top:35px">Tutors</h2><table class="widefat striped"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Subjects</th><th>Grades</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?php echo (int)$r->id; ?></td><td><?php echo esc_html($r->name); ?></td><td><?php echo esc_html($r->email); ?></td><td><?php echo nl2br(esc_html($r->subjects)); ?></td><td><?php echo nl2br(esc_html($r->grade_levels)); ?></td><td><?php echo esc_html($r->status); ?></td><td><a href="<?php echo esc_url(admin_url('admin.php?page=blututor-tutors&edit='.$r->id)); ?>">Edit</a> | <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=blututor_delete_tutor&id='.$r->id),'blututor_delete_tutor_'.$r->id)); ?>" onclick="return confirm('Delete this tutor and their matches?');">Delete</a></td></tr><?php endforeach; ?></tbody></table></div><?php
    }
    public function save(){ if(!current_user_can('manage_options'))wp_die('Unauthorized'); check_admin_referer('blututor_save_tutor'); global $wpdb; $id=absint($_POST['id']??0); $data=['name'=>sanitize_text_field(wp_unslash($_POST['name']??'')),'email'=>sanitize_email(wp_unslash($_POST['email']??'')),'phone'=>sanitize_text_field(wp_unslash($_POST['phone']??'')),'subjects'=>sanitize_textarea_field(wp_unslash($_POST['subjects']??'')),'grade_levels'=>sanitize_textarea_field(wp_unslash($_POST['grade_levels']??'')),'status'=>sanitize_text_field(wp_unslash($_POST['status']??'Pending')),'availability'=>sanitize_textarea_field(wp_unslash($_POST['availability']??'')),'notes'=>sanitize_textarea_field(wp_unslash($_POST['notes']??'')),'updated_at'=>current_time('mysql')]; if($id)$wpdb->update(blututor_core_table('tutors'),$data,['id'=>$id]);else{$data['created_at']=current_time('mysql');$wpdb->insert(blututor_core_table('tutors'),$data);} wp_safe_redirect(admin_url('admin.php?page=blututor-tutors'));exit; }
    public function delete(){ $id=absint($_GET['id']??0);if(!current_user_can('manage_options'))wp_die('Unauthorized');check_admin_referer('blututor_delete_tutor_'.$id);global $wpdb;$wpdb->delete(blututor_core_table('matches'),['tutor_id'=>$id]);$wpdb->delete(blututor_core_table('tutors'),['id'=>$id]);wp_safe_redirect(admin_url('admin.php?page=blututor-tutors'));exit; }
}