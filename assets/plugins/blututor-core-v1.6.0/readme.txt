=== BluTutor Core ===
Contributors: blututor
Tags: tutoring, students, tutors, matching, tutor applications
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 1.6.0
License: GPLv2 or later

BluTutor Core v1.6.0 adds the tutor application workflow while preserving the existing student, matching, selection, and notification workflow.

Tutor Applications:
- Public shortcode: [blututor_tutor_application]
- REST endpoint: /wp-json/blututor/v1/tutor-applications
- Admin review statuses: Applicant, Reviewing, Needs Information, Approved, Rejected.
- Approved applications can be converted into Approved tutors.
- Only Approved or Active tutors remain eligible for matching.

Existing email notifications and matching behavior are preserved.