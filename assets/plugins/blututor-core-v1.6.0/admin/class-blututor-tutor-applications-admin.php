<?php
if (!defined('ABSPATH')) exit;

class BluTutor_Tutor_Applications_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_post_blututor_application_status', [$this, 'status']);
        add_action('admin_post_blututor_application_create_tutor', [$this, 'create_tutor']);
    }

    public function menu() {
        add_submenu_page('blututor-students','Tutor Applications','Tutor Applications','manage_options','blututor-tutor-applications',[$this, 'page']);
    }

    public function page() {
        if (!current_user_can('manage_options')) return;
        global $wpdb;
        $table = blututor_core_table('tutor_applications');
        $status_filter = sanitize_text_field(wp_unslash($_GET['status'] ?? ''));
        $where = ''; $params = [];
        if ($status_filter !== '') { $where = ' WHERE status=%s'; $params[] = $status_filter; }
        $sql = "SELECT * FROM $table$where ORDER BY created_at DESC";
        $rows = $params ? $wpdb->get_results($wpdb->prepare($sql, ...$params)) : $wpdb->get_results($sql);
        $statuses = ['Applicant','Reviewing','Needs Information','Approved','Rejected'];
        ?>
        <div class="wrap">
        <h1 class="wp-heading-inline">BluTutor Tutor Applications</h1><hr class="wp-header-end">
        <p>Review tutor applicants before they become eligible for student matching.</p>
        <p><strong>Filter:</strong> <a href="<?php echo esc_url(admin_url('admin.php?page=blututor-tutor-applications')); ?>">All</a>
        <?php foreach ($statuses as $status): ?> | <a href="<?php echo esc_url(add_query_arg(['page'=>'blututor-tutor-applications','status'=>$status], admin_url('admin.php'))); ?>"><?php echo esc_html($status); ?></a><?php endforeach; ?></p>
        <table class="widefat striped"><thead><tr><th>ID</th><th>Applicant</th><th>Subjects</th><th>Grades</th><th>Experience</th><th>Rate</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        <?php if (!$rows): ?><tr><td colspan="8">No tutor applications found.</td></tr>
        <?php else: foreach ($rows as $row): ?><tr>
        <td><?php echo (int)$row->id; ?></td><td><strong><?php echo esc_html($row->name); ?></strong><br><small><?php echo esc_html($row->email); ?></small><?php if ($row->phone): ?><br><small><?php echo esc_html($row->phone); ?></small><?php endif; ?></td>
        <td><?php echo nl2br(esc_html($row->subjects)); ?></td><td><?php echo nl2br(esc_html($row->grade_levels)); ?></td><td><?php echo esc_html(wp_trim_words($row->experience,18)); ?></td><td><?php echo esc_html($row->hourly_rate); ?></td><td><strong><?php echo esc_html($row->status); ?></strong></td>
        <td><details><summary>Review</summary><p><strong>Qualifications</strong><br><?php echo nl2br(esc_html($row->qualifications)); ?></p><p><strong>Availability</strong><br><?php echo nl2br(esc_html($row->availability)); ?></p><p><strong>Notes</strong><br><?php echo nl2br(esc_html($row->notes)); ?></p><p><strong>Submitted:</strong> <?php echo esc_html($row->created_at); ?></p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:8px;"><input type="hidden" name="action" value="blututor_application_status"><input type="hidden" name="id" value="<?php echo (int)$row->id; ?>"><?php wp_nonce_field('blututor_application_status_'.$row->id); ?><select name="status"><?php foreach ($statuses as $status): ?><option <?php selected($row->status,$status); ?>><?php echo esc_html($status); ?></option><?php endforeach; ?></select> <button class="button" type="submit">Update Status</button></form>
        <?php if (in_array($row->status,['Approved','Applicant','Reviewing','Needs Information'],true)): ?><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="blututor_application_create_tutor"><input type="hidden" name="id" value="<?php echo (int)$row->id; ?>"><?php wp_nonce_field('blututor_application_create_tutor_'.$row->id); ?><button class="button button-primary" type="submit" onclick="return confirm('Create this applicant as an Approved tutor?');">Create Tutor</button></form><?php endif; ?>
        </details></td></tr><?php endforeach; endif; ?></tbody></table></div><?php
    }

    public function status() {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        $id = absint($_POST['id'] ?? 0); check_admin_referer('blututor_application_status_'.$id);
        $allowed=['Applicant','Reviewing','Needs Information','Approved','Rejected'];
        $status=sanitize_text_field(wp_unslash($_POST['status']??'Applicant')); if(!in_array($status,$allowed,true))$status='Applicant';
        global $wpdb; $wpdb->update(blututor_core_table('tutor_applications'),['status'=>$status,'updated_at'=>current_time('mysql')],['id'=>$id]);
        wp_safe_redirect(admin_url('admin.php?page=blututor-tutor-applications')); exit;
    }

    public function create_tutor() {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        $id=absint($_POST['id']??0); check_admin_referer('blututor_application_create_tutor_'.$id);
        global $wpdb; $application=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.blututor_core_table('tutor_applications').' WHERE id=%d',$id));
        if(!$application) wp_die('Tutor application not found.');
        $existing=$wpdb->get_var($wpdb->prepare('SELECT id FROM '.blututor_core_table('tutors').' WHERE email=%s LIMIT 1',$application->email));
        if(!$existing){ blututor_core_insert_tutor(['name'=>$application->name,'email'=>$application->email,'phone'=>$application->phone,'subjects'=>$application->subjects,'grade_levels'=>$application->grade_levels,'status'=>'Approved','availability'=>$application->availability,'notes'=>trim('Qualifications: '.$application->qualifications."

Experience: ".$application->experience."

Rate: ".$application->hourly_rate."

Application ID: ".$application->id)]); }
        $wpdb->update(blututor_core_table('tutor_applications'),['status'=>'Approved','updated_at'=>current_time('mysql')],['id'=>$id]);
        wp_safe_redirect(admin_url('admin.php?page=blututor-tutor-applications')); exit;
    }
}