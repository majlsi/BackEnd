<?php

use Illuminate\Database\Seeder;

class RightsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rightsData = [
            ['id' => 1, 'right_name' => 'Add New Role', 'right_name_ar' => 'اضافه دور', 'module_id' => 1, 'right_url' => '/roles/add', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 2, 'right_name' => 'Roles', 'right_name_ar' => 'الادوار', 'module_id' => 1, 'right_url' => '/roles', 'right_order_number' => 5, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 3, 'right_name' => 'Edit Role', 'right_name_ar' => 'تعديل دور', 'module_id' => 1, 'right_url' => '/roles/edit/:id', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],

            ['id' => 4, 'right_name' => 'Add New User', 'right_name_ar' => 'اضافه مستخدم', 'module_id' => 6, 'right_url' => '/users/add', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 5, 'right_name' => 'Users', 'right_name_ar' => 'المستخدمين', 'module_id' => 6, 'right_url' => '/users', 'right_order_number' => 2, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 6, 'right_name' => 'Edit User', 'right_name_ar' => 'تعديل مستخدم', 'module_id' => 6, 'right_url' => '/users/edit/:id', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],


            ['id' => 7, 'right_name' => 'Pending requests', 'right_name_ar' => 'الطلبات الجديدة', 'module_id' => 2, 'right_url' => '/organizations/requests', 'right_order_number' => 2, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 8, 'right_name' => 'Approved requests', 'right_name_ar' => ' المنشآت  المفعلة', 'module_id' => 2, 'right_url' => '/organizations/1', 'right_order_number' => 3, 'in_menu' => 1, 'icon' => 'fa fa-check-square', 'right_type_id' => 1],
            ['id' => 9, 'right_name' => 'Rejected requests', 'right_name_ar' => ' المنشآت  الغير مفعلة', 'module_id' => 2, 'right_url' => '/organizations/0', 'right_order_number' => 4, 'in_menu' => 1, 'icon' => 'fa fa-minus-square', 'right_type_id' => 1],
            ['id' => 10, 'right_name' => 'Edit Organization', 'right_name_ar' => 'تعديل المنشأة', 'module_id' => 2, 'right_url' => 'organizations/edit/:id', 'right_order_number' => 5, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 1],

            ['id' => 13, 'right_name' => 'Add New Committee', 'right_name_ar' => 'إضافة لجنة جديدة', 'module_id' => 15, 'right_url' => '/committees/add', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 14, 'right_name' => 'Committees', 'right_name_ar' => 'اللجان', 'module_id' => 15, 'right_url' => '/committees', 'right_order_number' => 3, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 15, 'right_name' => 'Edit Committee', 'right_name_ar' => 'تعديل اللجنه', 'module_id' => 15, 'right_url' => '/committees/edit/:id', 'right_order_number' => 2, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],

            // ['id' => 16, 'right_name' => 'Add New Meeting Type', 'right_name_ar' => 'اضافه نوع اجتماع', 'module_id' => 1, 'right_url' => '/meeting-types/add', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            // ['id' => 17, 'right_name' => 'Meeting Types', 'right_name_ar' => 'أنواع الإجتماعات', 'module_id' => 1, 'right_url' => '/meeting-types', 'right_order_number' => 7, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            // ['id' => 18, 'right_name' => 'Edit Meeting Types', 'right_name_ar' => 'تعديل نوع اجتماع', 'module_id' => 1, 'right_url' => '/meeting-types/edit/:id', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],

            ['id' => 19, 'right_name' => 'Add New Time Zone', 'right_name_ar' => 'إضافة توقيت', 'module_id' => 1, 'right_url' => '/time-zones/add', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 20, 'right_name' => 'Time Zones', 'right_name_ar' => 'التوقيتات', 'module_id' => 1, 'right_url' => '/time-zones', 'right_order_number' => 6, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 21, 'right_name' => 'Edit Time Zone', 'right_name_ar' => 'تعديل توقيت', 'module_id' => 1, 'right_url' => '/time-zones/edit/:id', 'right_order_number' => 2, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 1],

            ['id' => 22, 'right_name' => 'Add New Meeting', 'right_name_ar' => 'إضافة اجتماع', 'module_id' => 5, 'right_url' => '/meetings/add', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 23, 'right_name' => 'Meetings', 'right_name_ar' => 'الإجتماعات', 'module_id' => 5, 'right_url' => '/meetings', 'right_order_number' => 2, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 24, 'right_name' => 'Edit Meeting', 'right_name_ar' => 'تعديل اجتماع', 'module_id' => 5, 'right_url' => '/meetings/edit/:id', 'right_order_number' => 2, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],

            ['id' => 25, 'right_name' => 'Activate User', 'right_name_ar' => 'تنشيط مستخدم', 'module_id' => 6, 'right_url' => '', 'right_order_number' => 4, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 26, 'right_name' => 'De-activate User', 'right_name_ar' => 'إلغاء تنشيط مستخدم', 'module_id' => 6, 'right_url' => '', 'right_order_number' => 5, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],

            ['id' => 27, 'right_name' => 'Delete Role', 'right_name_ar' => 'حذف دور', 'module_id' => 1, 'right_url' => '', 'right_order_number' => 4, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 28, 'right_name' => 'Delete User', 'right_name_ar' => 'حذف مستخدم', 'module_id' => 6, 'right_url' => '', 'right_order_number' => 4, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 29, 'right_name' => 'Delete Committee', 'right_name_ar' => 'حذف اللجنه', 'module_id' => 1, 'right_url' => '', 'right_order_number' => 4, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],

            ['id' => 30, 'right_name' => 'Meeting Dashboard', 'right_name_ar' => 'اجتماعاتى', 'module_id' => 5, 'right_url' => '/meeting-dashboard', 'right_order_number' => 1, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 31, 'right_name' => 'My Calendar', 'right_name_ar' => 'تقويمى', 'module_id' => 5, 'right_url' => '/meeting-calendar', 'right_order_number' => 4, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],


            ['id' => 32, 'right_name' => 'Organization', 'right_name_ar' => 'المنشأة', 'module_id' => 1, 'right_url' => '/edit-organization-profile', 'right_order_number' => 1, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 2],

            // ['id' => 33, 'right_name' => 'Add New Participant', 'right_name_ar' => 'اضافه مشترك', 'module_id' => 6, 'right_url' => '/participants/add', 'right_order_number' => 2, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            // ['id' => 34, 'right_name' => 'Participants', 'right_name_ar' => 'المشاركين', 'module_id' => 6, 'right_url' => '/participants', 'right_order_number' => 1, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            // ['id' => 35, 'right_name' => 'Edit Participant', 'right_name_ar' => 'تعديل مشترك', 'module_id' => 6, 'right_url' => '/participants/edit/:id', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],

            ['id' => 36, 'right_name' => 'Add New Proposal', 'right_name_ar' => 'اضافه مقترح', 'module_id' => 5, 'right_url' => '/proposals/add', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 37, 'right_name' => 'Proposals', 'right_name_ar' => 'المقترحات', 'module_id' => 5, 'right_url' => '/proposals', 'right_order_number' => 3, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 38, 'right_name' => 'Proposal details', 'right_name_ar' => 'تفاصيل المقترح', 'module_id' => 5, 'right_url' => '/proposals/view/:id', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],

            // ['id' => 39, 'right_name' => 'Delete Meeting Type', 'right_name_ar' => 'حذف نوع اجتماع', 'module_id' => 1, 'right_url' => '', 'right_order_number' => 4, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 40, 'right_name' => 'Delete Time Zone', 'right_name_ar' => 'حذف توقيت', 'module_id' => 1, 'right_url' => '', 'right_order_number' => 4, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 41, 'right_name' => 'Secertary Dashboard', 'right_name_ar' => 'لوحة التحكم للسكرتير', 'module_id' => 7, 'right_url' => '/dashboard/secertary_dashboard', 'right_order_number' => 1, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 42, 'right_name' => 'Participant Dashboard', 'right_name_ar' => 'لوحة التحكم للمشارك', 'module_id' => 7, 'right_url' => '/dashboard/participant_dashboard', 'right_order_number' => 2, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 43, 'right_name' => 'Admin Dashboard', 'right_name_ar' => 'لوحة التحكم للادمن', 'module_id' => 7, 'right_url' => '/dashboard/admin_dashboard', 'right_order_number' => 4, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 1],
            // ['id' => 44, 'right_name' => 'Add participants During Meeting', 'right_name_ar' => 'اضافة مشاركين أثناء اﻷجتماع', 'module_id' => 7, 'right_url' => '', 'right_order_number' => 4, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            // ['id' => 45, 'right_name' => 'View Attendance', 'right_name_ar' => 'عرض تفاصيل حضور اﻷجتماع', 'module_id' => 7, 'right_url' => '', 'right_order_number' => 4, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],

            ['id' => 46, 'right_name' => 'View Meeting', 'right_name_ar' => 'عرض تفاصيل اﻷجتماع', 'module_id' => 5, 'right_url' => '/view-meetings/:id', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            // ['id' => 47, 'right_name' => 'Meeting Organizer', 'right_name_ar' => 'امين المجلس', 'module_id' => 7, 'right_url' => '', 'right_order_number' => 4, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],

            ['id' => 48, 'right_name' => 'Meeting Absence', 'right_name_ar' => 'حضور الإجتماعات', 'module_id' => 5, 'right_url' => '/manage-absence', 'right_order_number' => 5, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],

            ['id' => 49, 'right_name' => 'Add New Job Title', 'right_name_ar' => 'اضافه وظيفة', 'module_id' => 1, 'right_url' => '/job-titles/add', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 50, 'right_name' => 'Job Titles', 'right_name_ar' => 'الوظائف', 'module_id' => 1, 'right_url' => '/job-titles', 'right_order_number' => 8, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 51, 'right_name' => 'Edit Job Title', 'right_name_ar' => 'تعديل الوظيفة', 'module_id' => 1, 'right_url' => '/job-titles/edit/:id', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],

            ['id' => 52, 'right_name' => 'Add New User Title', 'right_name_ar' => 'اضافه لقب', 'module_id' => 1, 'right_url' => '/user-titles/add', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 53, 'right_name' => 'User Titles', 'right_name_ar' => 'الألقاب', 'module_id' => 1, 'right_url' => '/user-titles', 'right_order_number' => 9, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 54, 'right_name' => 'Edit User Title', 'right_name_ar' => 'تعديل اللقب', 'module_id' => 1, 'right_url' => '/user-titles/edit/:id', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],

            ['id' => 55, 'right_name' => 'Add New Nickname', 'right_name_ar' => 'اضافه صفة', 'module_id' => 1, 'right_url' => '/nicknames/add', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 56, 'right_name' => 'Nicknames', 'right_name_ar' => 'الصفات', 'module_id' => 1, 'right_url' => '/nicknames', 'right_order_number' => 10, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 57, 'right_name' => 'Edit Nickname', 'right_name_ar' => 'تعديل الصفة', 'module_id' => 1, 'right_url' => '/nicknames/edit/:id', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 58, 'right_name' => 'Organization Dashboard', 'right_name_ar' => 'لوحة التحكم للمنشأة', 'module_id' => 7, 'right_url' => '/dashboard/admin_dashboard/organization_dashboard/:id', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 1],

            ['id' => 59, 'right_name' => 'Delete Job Title', 'right_name_ar' => 'حذف الوظيفة', 'module_id' => 1, 'right_url' => '', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            // ['id' => 60, 'right_name' => 'Delete Participant', 'right_name_ar' => 'حذف مشترك', 'module_id' => 6, 'right_url' => '', 'right_order_number' => 4, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 61, 'right_name' => 'Delete User Title', 'right_name_ar' => 'حذف اللقب', 'module_id' => 1, 'right_url' => '', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 62, 'right_name' => 'Delete Nickname', 'right_name_ar' => 'حذف الصفة', 'module_id' => 1, 'right_url' => '', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 63, 'right_name' => 'Delete Meeting', 'right_name_ar' => 'حذف اجتماع', 'module_id' => 5, 'right_url' => '', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 64, 'right_name' => 'Manage MOM', 'right_name_ar' => 'محضر الاجتماع', 'module_id' => 5, 'right_url' => '/meetings/mom/:id', 'right_order_number' => 2, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],

            ['id' => 65, 'right_name' => 'Add New Task', 'right_name_ar' => 'اضافه مهمة', 'module_id' => 8, 'right_url' => '', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 66, 'right_name' => 'Edit Task', 'right_name_ar' => 'تعديل مهمة', 'module_id' => 8, 'right_url' => '', 'right_order_number' => 2, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 67, 'right_name' => 'Delete Task', 'right_name_ar' => 'حذف مهمة', 'module_id' => 8, 'right_url' => '', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],

            ['id' => 68, 'right_name' => 'Organization Tasks', 'right_name_ar' => 'مهام المنشأة', 'module_id' => 8, 'right_url' => '/tasks-management/admin-dashboard', 'right_order_number' => 1, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 69, 'right_name' => 'My Tasks Dashboard', 'right_name_ar' => 'مهامي', 'module_id' => 8, 'right_url' => '/tasks-management/member-dashboard', 'right_order_number' => 2, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 70, 'right_name' => 'Task Details', 'right_name_ar' => 'تفاصيل المهمة', 'module_id' => 8, 'right_url' => '/tasks-management/task-details/:id', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],

            ['id' => 71, 'right_name' => 'Settings', 'right_name_ar' => 'الإعدادات', 'module_id' => 1, 'right_url' => '/settings', 'right_order_number' => 11, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 72, 'right_name' => 'Committee Details', 'right_name_ar' => 'تفاصيل اللجنة', 'module_id' => 8, 'right_url' => '/tasks-management/committee-details/:id', 'right_order_number' => 4, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 74, 'right_name' => 'All commitee tasks', 'right_name_ar' => 'جميع مهام اللجان', 'module_id' => 8, 'right_url' => '', 'right_order_number' => 5, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 75, 'right_name' => 'Tasks statistics', 'right_name_ar' => 'إحصائات المهام', 'module_id' => 8, 'right_url' => '/tasks-management/statistic', 'right_order_number' => 6, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 76, 'right_name' => 'All conversations', 'right_name_ar' => 'كل المحادثات', 'module_id' => 9, 'right_url' => '/conversations/chats', 'right_order_number' => 1, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            //['id' => 77, 'right_name' => 'Meetings conversations', 'right_name_ar' => 'محادثات الإجتماعات', 'module_id' => 9, 'right_url' => '/conversations/meetings', 'right_order_number' => 2, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 78, 'right_name' => 'Failed attempts', 'right_name_ar' => 'المحاولات الخاطئة', 'module_id' => 1, 'right_url' => '/blocked-users', 'right_order_number' => 12, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 79, 'right_name' => 'Remove attempt', 'right_name_ar' => 'حذف محاولة', 'module_id' => 1, 'right_url' => '', 'right_order_number' => 13, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 80, 'right_name' => 'Online configurations', 'right_name_ar' => 'اعدادات الاونلاين', 'module_id' => 1, 'right_url' => '/online-configurations', 'right_order_number' => 14, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 81, 'right_name' => 'Add online configurations', 'right_name_ar' => 'اضافة اعدادات الاجتماعات الاونلاين', 'module_id' => 1, 'right_url' => '/online-configurations/add', 'right_order_number' => 15, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 82, 'right_name' => 'Edit online configurations', 'right_name_ar' => 'تعديل اعدادات الاجتماعات الاونلاين', 'module_id' => 1, 'right_url' => '/online-configurations/:id', 'right_order_number' => 16, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 83, 'right_name' => 'Delete online configurations', 'right_name_ar' => 'حذف اعدادات الاجتماعات الاونلاين', 'module_id' => 1, 'right_url' => '', 'right_order_number' => 17, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],

            ['id' => 84, 'right_name' => 'Add New Mom Template', 'right_name_ar' => 'إضافة نموذج محضر الإجتماع', 'module_id' => 1, 'right_url' => '/mom-templates/add', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 85, 'right_name' => 'Mom Templates', 'right_name_ar' => 'نماذج محضر الإجتماع', 'module_id' => 1, 'right_url' => '/mom-templates', 'right_order_number' => 15, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 86, 'right_name' => 'Edit Mom Template', 'right_name_ar' => 'تعديل نموذج محضر الإجتماع', 'module_id' => 1, 'right_url' => '/mom-templates/edit/:id', 'right_order_number' => 2, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 87, 'right_name' => 'Delete Mom Template', 'right_name_ar' => 'حذف نموذج محضر الإجتماع', 'module_id' => 1, 'right_url' => '', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],

            ['id' => 88, 'right_name' => 'Add New Agenda Template', 'right_name_ar' => 'إضافة نموذج جدول الأعمال', 'module_id' => 1, 'right_url' => '/agenda-templates/add', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 89, 'right_name' => 'Agenda Templates', 'right_name_ar' => 'نماذج جدول الأعمال ', 'module_id' => 1, 'right_url' => '/agenda-templates', 'right_order_number' => 16, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 90, 'right_name' => 'Edit Agenda Template', 'right_name_ar' => 'تعديل نموذج جدول الأعمال', 'module_id' => 1, 'right_url' => '/agenda-templates/edit/:id', 'right_order_number' => 2, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 91, 'right_name' => 'Delete Agenda Template', 'right_name_ar' => 'حذف نموذج جدول الأعمال', 'module_id' => 1, 'right_url' => '', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],

            ['id' => 92, 'right_name' => 'Add New Mom Summary Template', 'right_name_ar' => 'إضافة نموذج ملخص محضر الإجتماع', 'module_id' => 1, 'right_url' => '/mom-summary-templates/add', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 93, 'right_name' => 'Mom Summary', 'right_name_ar' => 'ملخص محضر الإجتماع', 'module_id' => 1, 'right_url' => '/mom-summary-templates', 'right_order_number' => 17, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 94, 'right_name' => 'Edit Mom Summary Template', 'right_name_ar' => 'تعديل نموذج ملخص محضر الإجتماع', 'module_id' => 1, 'right_url' => '/mom-summary-templates/edit/:id', 'right_order_number' => 2, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 95, 'right_name' => 'Delete Mom Summary Template', 'right_name_ar' => 'حذف نموذج ملخص محضر الإجتماع', 'module_id' => 1, 'right_url' => '', 'right_order_number' => 1, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 96, 'right_name' => 'Reviews room', 'right_name_ar' => 'غرفة المراجعات', 'module_id' => 10, 'right_url' => '/reviews-room', 'right_order_number' => 1, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 97, 'right_name' => 'Add document', 'right_name_ar' => 'إضافة مستند', 'module_id' => 10, 'right_url' => '/reviews-room/add', 'right_order_number' => 2, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 98, 'right_name' => 'Edit document', 'right_name_ar' => 'تعديل مستند', 'module_id' => 10, 'right_url' => '/reviews-room/edit/{id}', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 99, 'right_name' => 'Delete document', 'right_name_ar' => 'حذف مستند', 'module_id' => 10, 'right_url' => '', 'right_order_number' => 4, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 100, 'right_name' => 'Review room', 'right_name_ar' => 'غرفة المراجعة', 'module_id' => 10, 'right_url' => '/reviews-room/details/{id}', 'right_order_number' => 5, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 101, 'right_name' => 'Decisions', 'right_name_ar' => ' القرارات', 'module_id' => 11, 'right_url' => '/decisions', 'right_order_number' => 1, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],

            ['id' => 102, 'right_name' => 'Decision types', 'right_name_ar' => 'أنواع القرارات', 'module_id' => 1, 'right_url' => '/decision-types', 'right_order_number' => 18, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 103, 'right_name' => 'Add decision type', 'right_name_ar' => 'إضافه نوع قرار', 'module_id' => 1, 'right_url' => '/decision-types/add', 'right_order_number' => 19, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 104, 'right_name' => 'Edit decision type', 'right_name_ar' => 'تعديل نوع قرار', 'module_id' => 1, 'right_url' => '/decision-types/edit/:id', 'right_order_number' => 20, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 105, 'right_name' => 'Delete decision type', 'right_name_ar' => 'حذف نوع قرار', 'module_id' => 1, 'right_url' => '', 'right_order_number' => 21, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 106, 'right_name' => 'Circular decisions', 'right_name_ar' => ' قرارات بالتمرير', 'module_id' => 11, 'right_url' => '/circular-decisions', 'right_order_number' => 2, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 107, 'right_name' => 'Add circular decision', 'right_name_ar' => ' إضافه قرار بالتمرير', 'module_id' => 11, 'right_url' => '/circular-decisions/add', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 108, 'right_name' => 'Edit circular decision', 'right_name_ar' => ' تعديل قرار بالتمرير', 'module_id' => 11, 'right_url' => '/circular-decisions/edit/:id', 'right_order_number' => 4, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 109, 'right_name' => 'Delete circular decision', 'right_name_ar' => ' حذف قرار بالتمرير', 'module_id' => 11, 'right_url' => '', 'right_order_number' => 5, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 110, 'right_name' => 'Circular decision details', 'right_name_ar' => ' تفاصيل قرار بالتمرير', 'module_id' => 11, 'right_url' => '/circular-decisions/details/:id', 'right_order_number' => 6, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 111, 'right_name' => 'Board Dashaboard', 'right_name_ar' => ' لوحة تحكم مجلس إدارة الشركة', 'module_id' => 7, 'right_url' => '/dashboard/board', 'right_order_number' => 5, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 112, 'right_name' => 'Committees Dashboard', 'right_name_ar' => 'لوحة تحكم اللجان', 'module_id' => 7, 'right_url' => '/dashboard/committees', 'right_order_number' => 6, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 113, 'right_name' => 'Member Dashboard', 'right_name_ar' => ' لوحة قيادة العضو', 'module_id' => 7, 'right_url' => '/dashboard/member', 'right_order_number' => 7, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 114, 'right_name' => 'Files', 'right_name_ar' => 'الملفات', 'module_id' => 12, 'right_url' => '/files/my', 'right_order_number' => 0, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],

            ['id' => 115, 'right_name' => 'Faq Sections', 'right_name_ar' => 'أقسام الأسئلة الشائعة', 'module_id' => 13, 'right_url' => '/faq-sections', 'right_order_number' => 1, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 116, 'right_name' => 'Add faq section', 'right_name_ar' => 'إضافه قسم للأسئلة الشائعة', 'module_id' => 13, 'right_url' => '/faq-sections/add', 'right_order_number' => 2, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 117, 'right_name' => 'Edit faq section', 'right_name_ar' => 'تعديل قسم في الأسئلة الشائعة', 'module_id' => 13, 'right_url' => '/faq-sections/edit/:id', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 118, 'right_name' => 'Delete faq section', 'right_name_ar' => 'حذف قسم من الأسئلة الشائعة', 'module_id' => 13, 'right_url' => '', 'right_order_number' => 4, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 1],


            ['id' => 119, 'right_name' => 'Faqs', 'right_name_ar' => 'الأسئلة الشائعة', 'module_id' => 13, 'right_url' => '/faqs', 'right_order_number' => 1, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 120, 'right_name' => 'Add faq', 'right_name_ar' => 'إضافه سؤال', 'module_id' => 13, 'right_url' => '/faqs/add', 'right_order_number' => 2, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 121, 'right_name' => 'Edit faq', 'right_name_ar' => 'تعديل سؤال', 'module_id' => 13, 'right_url' => '/faqs/edit/:id', 'right_order_number' => 3, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 122, 'right_name' => 'Delete faq', 'right_name_ar' => 'حذف سؤال', 'module_id' => 13, 'right_url' => '', 'right_order_number' => 4, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 1],

            ['id' => 123, 'right_name' => 'Videos guide', 'right_name_ar' => 'فيديوهات توضيحية', 'module_id' => 13, 'right_url' => '/videos-guide', 'right_order_number' => 5, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 124, 'right_name' => 'Add video guide', 'right_name_ar' => 'إضافه فيديو توضيحى', 'module_id' => 13, 'right_url' => '/videos-guide/add', 'right_order_number' => 6, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 125, 'right_name' => 'Edit video guide', 'right_name_ar' => 'تعديل فيديو توضيحى', 'module_id' => 13, 'right_url' => '/videos-guide/edit/:id', 'right_order_number' => 7, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 126, 'right_name' => 'Delete video guide', 'right_name_ar' => 'حذف فيديو توضيحى', 'module_id' => 13, 'right_url' => '', 'right_order_number' => 8, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => 1],
            ['id' => 127, 'right_name' => 'Faqs', 'right_name_ar' => 'الأسئلة الشائعة', 'module_id' => 13, 'right_url' => '/help-center/faqs', 'right_order_number' => 9, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 128, 'right_name' => 'Guide Videos', 'right_name_ar' => 'فيديوهات توضيحية', 'module_id' => 13, 'right_url' => '/help-center/tutorials', 'right_order_number' => 10, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 2],

            ['id' => 129, 'right_name' => 'Add New Stakeholder', 'right_name_ar' => 'اضافه مساهم', 'module_id' => 6, 'right_url' => '/Stakeholders/add', 'right_order_number' => 6, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 130, 'right_name' => 'Stakeholders', 'right_name_ar' => 'المساهمين', 'module_id' => 6, 'right_url' => '/Stakeholders', 'right_order_number' => 7, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 131, 'right_name' => 'Edit Stakeholder', 'right_name_ar' => 'تعديل مساهم', 'module_id' => 6, 'right_url' => '/Stakeholders/edit/:id', 'right_order_number' => 8, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 132, 'right_name' => 'Activate Stakeholder', 'right_name_ar' => 'تنشيط مساهم', 'module_id' => 6, 'right_url' => '', 'right_order_number' => 9, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 133, 'right_name' => 'De-activate Stakeholder', 'right_name_ar' => 'إلغاء تنشيط مساهم', 'module_id' => 6, 'right_url' => '', 'right_order_number' => 10, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 134, 'right_name' => 'Delete Stakeholder', 'right_name_ar' => 'حذف مساهم', 'module_id' => 6, 'right_url' => '', 'right_order_number' => 11, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],

            /* Guest Rights */
            ['id' => 137, 'right_name' => 'Presenting', 'right_name_ar' => 'تقديم عرض', 'module_id' => 5, 'right_url' => '', 'right_order_number' => 14, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 138, 'right_name' => 'Talking In Meeting', 'right_name_ar' => 'المحادثة داخل الاجتماع', 'module_id' => 5, 'right_url' => '', 'right_order_number' => 15, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 139, 'right_name' => 'Can Show Mom', 'right_name_ar' => 'الاطلاع محضر الاجتماع', 'module_id' => 5, 'right_url' => '', 'right_order_number' => 16, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 140, 'right_name' => 'Can Voting', 'right_name_ar' => 'التصويت', 'module_id' => 5, 'right_url' => '', 'right_order_number' => 17, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 141, 'right_name' => 'Can Show Voting Results', 'right_name_ar' => 'الاطلاع على نتائج التصويت', 'module_id' => 5, 'right_url' => '', 'right_order_number' => 18, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            /* End Guest Rights */

            /* Meeting Page Rights */
            ['id' => 142, 'right_name' => 'Organizations Section', 'right_name_ar' => 'قسم المنظمين', 'module_id' => 5, 'right_url' => '', 'right_order_number' => 19, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 143, 'right_name' => 'Participants Section', 'right_name_ar' => 'قسم الاعضاء', 'module_id' => 5, 'right_url' => '', 'right_order_number' => 20, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 144, 'right_name' => 'Attachments Section', 'right_name_ar' => 'قسم المرفقات', 'module_id' => 5, 'right_url' => '', 'right_order_number' => 21, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 145, 'right_name' => 'Agendas Section', 'right_name_ar' => 'قسم جدول الأعمال', 'module_id' => 5, 'right_url' => '', 'right_order_number' => 22, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 146, 'right_name' => 'Voters Results Section', 'right_name_ar' => 'قسم نتائج الناخبين', 'module_id' => 5, 'right_url' => '', 'right_order_number' => 23, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            /* End Meeting Page Rights */

            /* Approval rights */
            ['id' => 147, 'right_name' => 'Approvals', 'right_name_ar' => 'الموافقات', 'module_id' => 14, 'right_url' => '/approvals', 'right_order_number' => 24, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 148, 'right_name' => 'Add approval', 'right_name_ar' => 'إضافة موافقة', 'module_id' => 14, 'right_url' => '/approvals/add', 'right_order_number' => 25, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 149, 'right_name' => 'Approval', 'right_name_ar' => 'الموافقة', 'module_id' => 14, 'right_url' => '/approvals/details/{id}', 'right_order_number' => 26, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            /* End Approval rights */

            /* Committee Requests Rights */
            ['id' => 150, 'right_name' => 'Permanent committees', 'right_name_ar' => 'اللجان الدائمة', 'module_id' => 15, 'right_url' => '/permanent-committee', 'right_order_number' => 27, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 151, 'right_name' => 'Temporary committee', 'right_name_ar' => 'اللجان المؤقتة', 'module_id' => 15, 'right_url' => '/temporary-committee', 'right_order_number' => 28, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 2],
            ['id' => 152, 'right_name' => 'Committee requests', 'right_name_ar' => 'طلبات اللجان', 'module_id' => 15, 'right_url' => '/committee-requests', 'right_order_number' => 29, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 2],
            /* End Committee Requests Rights */


            /*Block User*/
            ['id' => 153, 'right_name' => 'Block User', 'right_name_ar' => 'تجميد مستخدم', 'module_id' => 6, 'right_url' => '', 'right_order_number' => 30, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            /*End Block user*/

            ['id' => 154, 'right_name' => 'Committees Dashboard', 'right_name_ar' => 'لوحة تحكم اللجان', 'module_id' => 7, 'right_url' => '/dashboard/committee_dashboard', 'right_order_number' => 2, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 157, 'right_name' => 'User Evaluation', 'right_name_ar' => 'تقييم المستخدم', 'module_id' => 15, 'right_url' => '', 'right_order_number' => 0, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],

            ['id' => 155, 'right_name' => 'Committee Export', 'right_name_ar' => 'تصدير اللجنة', 'module_id' => 15, 'right_url' => '/committees', 'right_order_number' => 2, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],


            ['id' => 156, 'right_name' => 'Reminder final committee work', 'right_name_ar' => 'تذكير اخر ما تم من اعمال اللجنه', 'module_id' => 15, 'right_url' => '', 'right_order_number' => 2, 'in_menu' => 0, 'icon' => ' ', 'right_type_id' => null],
            ['id' => 158, 'right_name' => 'My Committees', 'right_name_ar' => 'لجانى', 'module_id' => 15, 'right_url' => '/committees/my-committees', 'right_order_number' => 4, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => null],

            // Audit
            ['id' => 159, 'right_name' => 'History', 'right_name_ar' => 'السجل', 'module_id' => 16, 'right_url' => '/history', 'right_order_number' => 0, 'in_menu' => 1, 'icon' => ' ', 'right_type_id' => 2],

        ];

        foreach ($rightsData as $key => $right) {
            $exists = DB::table('rights')->where('id', $right['id'])->first();
            if (!$exists) {
                DB::table('rights')->insert([$right]);
            } else {
                DB::table('rights')
                    ->where('id', $right['id'])
                    ->update([
                        'right_name' => $right['right_name'], 'right_name_ar' => $right['right_name_ar'], 'module_id' => $right['module_id'], 'right_url' => $right['right_url'], 'right_order_number' => $right['right_order_number'], 'in_menu' => $right['in_menu'], 'icon' => $right['icon'], 'right_type_id' => $right['right_type_id']
                    ]);
            }
        }
    }
}
