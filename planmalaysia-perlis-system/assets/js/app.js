/**
 * ========================================================================
 * SKRIP JAVASCRIPT APLIKASI (app.js)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // 1. TOGOL MENU SISI (SIDEBAR MOBILE)
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarNav = document.getElementById('sidebarNav');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');

    if (sidebarToggle && sidebarNav) {
        sidebarToggle.addEventListener('click', function () {
            sidebarNav.classList.toggle('show');
        });
    }

    if (sidebarCloseBtn && sidebarNav) {
        sidebarCloseBtn.addEventListener('click', function () {
            sidebarNav.classList.remove('show');
        });
    }

    // 2. TOGOL LIHAT KATA LALUAN (LOGIN PAGE)
    const togglePasswordBtn = document.getElementById('togglePasswordBtn');
    const passwordInput = document.getElementById('password');
    const togglePasswordIcon = document.getElementById('togglePasswordIcon');

    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            if (togglePasswordIcon) {
                togglePasswordIcon.classList.toggle('bi-eye-fill', !isPassword);
                togglePasswordIcon.classList.toggle('bi-eye-slash-fill', isPassword);
            }
        });
    }

    // 3. BUTANG CEPAT PENGISIAN AKAUN DEMO (LOGIN TESTING)
    const demoBtns = document.querySelectorAll('.demo-fill-btn');
    demoBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const emailInput = document.getElementById('email');
            const passInput = document.getElementById('password');
            if (emailInput && passInput) {
                emailInput.value = this.getAttribute('data-email');
                passInput.value = this.getAttribute('data-pass');
                // Beri efek highlight
                emailInput.focus();
            }
        });
    });

    // 4. KEMASKINI MODAL PROJEK (POPULATE FORM)
    const editProjectBtns = document.querySelectorAll('.btn-edit-project');
    const editProjectModalEl = document.getElementById('editProjectModal');
    if (editProjectBtns.length && editProjectModalEl) {
        const editModal = new bootstrap.Modal(editProjectModalEl);
        editProjectBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const data = JSON.parse(this.getAttribute('data-project'));
                document.getElementById('edit_project_id').value = data.id;
                document.getElementById('edit_code').value = data.code;
                document.getElementById('edit_title').value = data.title;
                document.getElementById('edit_category').value = data.category;
                document.getElementById('edit_lead_user_id').value = data.lead_user_id || '';
                document.getElementById('edit_description').value = data.description || '';
                document.getElementById('edit_start_date').value = data.start_date || '';
                document.getElementById('edit_end_date').value = data.end_date || '';
                document.getElementById('edit_budget').value = data.budget || '';
                document.getElementById('edit_status').value = data.status;
                document.getElementById('edit_progress').value = data.progress || 0;
                editModal.show();
            });
        });
    }

    // 5. PENGESAHAN PADAM PROJEK
    const deleteProjectBtns = document.querySelectorAll('.btn-delete-project');
    const deleteProjectModalEl = document.getElementById('deleteProjectModal');
    if (deleteProjectBtns.length && deleteProjectModalEl) {
        const delModal = new bootstrap.Modal(deleteProjectModalEl);
        deleteProjectBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                document.getElementById('delete_project_id').value = id;
                document.getElementById('delete_project_title').textContent = title;
                delModal.show();
            });
        });
    }

    // 6. KEMASKINI MODAL TUGASAN
    const editTaskBtns = document.querySelectorAll('.btn-edit-task');
    const editTaskModalEl = document.getElementById('editTaskModal');
    if (editTaskBtns.length && editTaskModalEl) {
        const editTaskModal = new bootstrap.Modal(editTaskModalEl);
        editTaskBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const task = JSON.parse(this.getAttribute('data-task'));
                document.getElementById('edit_task_id').value = task.id;
                document.getElementById('edit_task_title').value = task.title;
                document.getElementById('edit_task_project_id').value = task.project_id || '';
                document.getElementById('edit_task_assigned_user_id').value = task.assigned_user_id || '';
                document.getElementById('edit_task_description').value = task.description || '';
                document.getElementById('edit_task_priority').value = task.priority;
                document.getElementById('edit_task_status').value = task.status;
                document.getElementById('edit_task_due_date').value = task.due_date || '';
                editTaskModal.show();
            });
        });
    }

    // 7. PENGESAHAN PADAM TUGASAN
    const deleteTaskBtns = document.querySelectorAll('.btn-delete-task');
    const deleteTaskModalEl = document.getElementById('deleteTaskModal');
    if (deleteTaskBtns.length && deleteTaskModalEl) {
        const delTaskModal = new bootstrap.Modal(deleteTaskModalEl);
        deleteTaskBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                document.getElementById('delete_task_id').value = id;
                document.getElementById('delete_task_title').textContent = title;
                delTaskModal.show();
            });
        });
    }

    // 8. KEMASKINI MODAL KAKITANGAN (USERS)
    const editUserBtns = document.querySelectorAll('.btn-edit-user');
    const editUserModalEl = document.getElementById('editUserModal');
    if (editUserBtns.length && editUserModalEl) {
        const editUserModal = new bootstrap.Modal(editUserModalEl);
        editUserBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const u = JSON.parse(this.getAttribute('data-user'));
                document.getElementById('edit_user_id').value = u.id;
                document.getElementById('edit_user_name').value = u.name;
                document.getElementById('edit_user_email').value = u.email;
                document.getElementById('edit_user_role').value = u.role;
                document.getElementById('edit_user_unit').value = u.unit || '';
                document.getElementById('edit_user_phone').value = u.phone || '';
                document.getElementById('edit_user_status').value = u.status || 'Aktif';
                editUserModal.show();
            });
        });
    }

    // 9. PENGESAHAN PADAM KAKITANGAN
    const deleteUserBtns = document.querySelectorAll('.btn-delete-user');
    const deleteUserModalEl = document.getElementById('deleteUserModal');
    if (deleteUserBtns.length && deleteUserModalEl) {
        const delUserModal = new bootstrap.Modal(deleteUserModalEl);
        deleteUserBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                document.getElementById('delete_user_id').value = id;
                document.getElementById('delete_user_name_text').textContent = name;
                delUserModal.show();
            });
        });
    }

    // 10. AUTO-HIDE NOTIFIKASI FLASH SELEPAS 6 SAAT
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 6000);
    });
});
