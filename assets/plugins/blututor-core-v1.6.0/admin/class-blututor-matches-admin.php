<?php
if (!defined('ABSPATH')) exit;
class BluTutor_Matches_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_post_blututor_match_status', [$this, 'status']);
    }
    public function menu() { add_submenu_page('blututor-students','Matches','Matches','manage_options','blututor-matches',[$this,'page']); }
    public function page() {
        if (!current_user_can('manage_options')) return;
        global $wpdb; $m=blututor_core_table('matches'); $s=blututor_core_table('students'); $t=blututor_core_table('tutors');
        $rows=$wpdb->get_results("SELECT m.*,s.name student_name,s.email student_email,s.subject student_subject,s.grade_level student_grade,t.name tutor_name,t.email tutor_email,t.subjects tutor_subjects,t.grade_levels tutor_grades FROM $m m JOIN $s s ON s.id=m.student_id JOIN $t t ON t.id=m.tutor_id ORDER BY m.created_at DESC");
        ?>
        <div class="wrap"><h1>BluTutor Matches</h1><table class="widefat striped"><thead><tr><th>ID</th><th>Student</th><th>Tutor</th><th>Score</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        <?php foreach($rows as $r): ?><tr><td><?php echo (int)$r->id; ?></td><td><?php echo esc_html($r->student_name); ?><br><small><?php echo esc_html($r->student_email); ?></small></td><td><?php echo esc_html($r->tutor_name); ?><br><small><?php echo esc_html($r->tutor_email); ?></small></td><td><?php echo esc_html($r->score); ?>/100</td><td><?php echo esc_html($r->status); ?></td><td><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="blututor_match_status"><input type="hidden" name="id" value="<?php echo (int)$r->id; ?>"><?php wp_nonce_field('blututor_match_status_'.$r->id); ?><select name="status"><?php foreach(['Suggested','Selected','Rejected'] as $st): ?><option <?php selected($r->status,$st); ?>><?php echo esc_html($st); ?></option><?php endforeach; ?></select> <button class="button">Update</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php
    }
    public function status() {
        if(!current_user_can('manage_options'))wp_die('Unauthorized'); $id=absint($_POST['id']??0); check_admin_referer('blututor_match_status_'.$id); $status=sanitize_text_field(wp_unslash($_POST['status']??'Suggested'));
        global $wpdb; $table=blututor_core_table('matches'); $old=$wpdb->get_var($wpdb->prepare("SELECT status FROM $table WHERE id=%d",$id)); $wpdb->update($table,['status'=>$status,'updated_at'=>current_time('mysql')],['id'=>$id]);
        if($status==='Selected' && $old!=='Selected') blututor_core_send_match_notifications($id);
        wp_safe_redirect(admin_url('admin.php?page=blututor-matches')); exit;
    }
}