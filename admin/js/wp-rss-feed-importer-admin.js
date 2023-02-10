document.addEventListener("DOMContentLoaded", function () {
    (function ($) {
        function xhr_admin_ajax_handle(data, is_formular = true, callback) {
            let xhr = new XMLHttpRequest();
            let formData = new FormData();
            xhr.open('POST', rss_ajax_obj.ajax_url, true);
            if (is_formular) {
                let input = new FormData(data);
                for (let [name, value] of input) {
                    formData.append(name, value);
                }
            } else {
                for (let [name, value] of Object.entries(data)) {
                    formData.append(name, value);
                }
            }
            xhr.onreadystatechange = function () {
                if (this.readyState === 4 && this.status === 200) {
                    if (typeof callback === 'function') {
                        xhr.addEventListener("load", callback);
                        return false;
                    }
                }
            }
            formData.append('_ajax_nonce', rss_ajax_obj.nonce);
            formData.append('action', 'RssImporter');
            xhr.send(formData);
        }

        $(document).on('submit', '.rss-feed-cron-form', function (event) {
            let form = $(this).closest("form").get(0);
            xhr_admin_ajax_handle(form, true, cron_settings_callback)
            event.preventDefault()
        })

        function cron_settings_callback() {
            let data = JSON.parse(this.responseText);
            let formClass = $(".rss-feed-cron-form");
            switch (data.type) {
                case 'rss_import_handle':
                    if (data.handle === 'insert') {
                        formClass.trigger('reset');
                    }
                    if (data.handle === 'update') {
                        if (data.status) {
                            dataTableRssOverview.draw('page');
                            new bootstrap.Collapse('#colImportOverview', {
                                toggle: true,
                                parent: '#collParent'
                            })
                        }
                    }
                    break;
            }
            swal_alert_response(data)
        }

        $(document).on('change', '#selectPostType', function () {

            let formData = {
                'method': 'get_taxonomy_select',
                'post_type': $(this).val()
            }
            xhr_admin_ajax_handle(formData, false, change_select_type_callback)
        });

        function change_select_type_callback() {
            let data = JSON.parse(this.responseText);
            let selTax = $('#selectPostTaxonomy');
            if (data.status) {
                let html = '';
                $.each(data.select, function (key, value) {
                    html += `<option value="${value.term_id}">${value.name}</option>`;
                });
                selTax.html(html);
            } else {
                selTax.html('');
            }
        }


        $(document).on('click', '.btn-toggle', function () {
            $('.btn-toggle').prop('disabled', false);
            $(this).prop('disabled', true);
            let parent = $(this).attr('data-parent');
            let target = $(this).attr('data-target');
            let type = $(this).attr('data-type');
            let formData;
            switch (type) {
                case 'overview':
                    dataTableRssOverview.draw('page')
                    break;
                case 'import_settings':
                case 'import_handle':
                    formData = {
                        'method': type,
                        'target': target,
                        'parent': parent,
                        'handle': 'insert'
                    };
                    break;

            }
            if (formData) {
                xhr_admin_ajax_handle(formData, false, btn_toggle_callback)
            } else {
                new bootstrap.Collapse(target, {
                    toggle: true,
                    parent: parent
                })
            }
        });

        function btn_toggle_callback() {
            let data = JSON.parse(this.responseText);
            let target;
            let endtime;
            let importWrapper = $('#colAddImport');
            switch (data.type) {
                case 'import_settings':
                    $('#colImportSettings').html(data.template);
                    endtime = new Date(data.next_time);
                    initializeClock('#nextSyncTime', endtime);

                    break;
                case 'import_handle':
                    importWrapper.html(data.template);
                    break;
                case 'get_next_sync_time':
                    if (data.target) {
                        endtime = new Date(data.next_time);
                        initializeClock(data.target, endtime);
                    }
                    return false;
            }

            if (data.status) {
                new bootstrap.Collapse(data.target, {
                    toggle: true,
                    parent: data.parent
                })
            } else {
                warning_message(data.msg)
            }
        }

        let rssTableId = $('#dataTablesRss');
        if (rssTableId) {
            rssTable();
        }

        $(document).on('click', '.rss-action', function () {
            let type = $(this).attr('data-type');
            let formData;
            let id;
            let target;
            let parent;
            let spinner = $('i ', $(this));
            let colTable = $('#colImportOverview');
            $(this).attr('data-id') ? id = $(this).attr('data-id') : id = '';
            $(this).attr('data-target') ? target = $(this).attr('data-target') : target = '';
            $(this).attr('data-parent') ? parent = $(this).attr('data-parent') : parent = '';
            switch (type) {
                case'update_import_handle':
                    formData = {
                        'method': 'import_handle',
                        'target': target,
                        'parent': parent,
                        'handle': 'update',
                        'id': id
                    };
                    break;
                case 'collapse-icon':
                    let colIcon = $('i ', $(this));
                    if (colIcon.hasClass('bi-arrows-expand')) {
                        $(this).addClass('active')
                        colIcon.removeClass('bi-arrows-expand').addClass('bi-arrows-collapse')
                    } else {
                        $(this).removeClass('active')
                        colIcon.addClass('bi-arrows-expand').removeClass('bi-arrows-collapse')
                    }
                    break;
                case 'backToTable':
                    dataTableRssOverview.draw('page')
                    new bootstrap.Collapse(colTable, {
                        toggle: true,
                        parent: '#collParent'
                    })
                    break;
                case'import_feeds_now':
                    spinner.addClass('spin');
                    formData = {
                        'method': type,
                        'id': id
                    }
                    break;
                case 'delete_import_feeds':
                    formData = {
                        'title': rss_ajax_obj.js_lang.delete_title,
                        'html': `<span class="swal-delete-body">${rss_ajax_obj.js_lang.delete_subtitle}</span>`,
                        'btnText': rss_ajax_obj.js_lang.delete_btn_txt,
                        'confirm_dialog': true
                    }
                    swal_delete_checkbox(formData).then((result) => {
                        if (result !== undefined) {
                            formData = {
                                'method': type,
                                'delete_posts': result,
                                'id': id,
                            }
                            //bi-arrow-repeat
                            spinner.addClass('spin bi-arrow-repeat');
                            xhr_admin_ajax_handle(formData, false, btn_action_callback)
                        }
                    });
                    return false;
            }
            if (formData) {
                xhr_admin_ajax_handle(formData, false, btn_action_callback)
            }
        })

        function btn_action_callback() {
            let data = JSON.parse(this.responseText);
            let importEditWrapper = $('#colEditImport');
            let rssActionIcon =  $('.rss-action i');
            rssActionIcon.removeClass('spin');

                switch (data.type) {
                    case 'import_handle':
                        if (data.status) {
                            importEditWrapper.html(data.template);
                            new bootstrap.Collapse(data.target, {
                                toggle: true,
                                parent: data.parent
                            })
                        } else {
                            warning_message(data.msg)
                        }
                        break;
                    case 'import_feeds_now':
                        dataTableRssOverview.draw('page')
                        swal_alert_response(data);
                        break;
                    case 'delete_import_feeds':
                        rssActionIcon.removeClass('bi-arrow-repeat');
                        dataTableRssOverview.draw('page')
                        swal_alert_response(data);
                        break;
                }
        }

        function swal_alert_response(data) {
            if (data.status) {
                Swal.fire({
                    position: 'top-end',
                    title: data.title,
                    text: data.msg,
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    customClass: {
                        popup: 'bg-light'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                }).then();
            } else {
                Swal.fire({
                    position: 'top-end',
                    title: data.title,
                    text: data.msg,
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false,
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    customClass: {
                        popup: 'swal-error-container'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                }).then();
            }
        }


        async function swal_delete_checkbox(data) {
            const {value: accept} = await Swal.fire({
                input: 'checkbox',
                title: data.title,
                reverseButtons: true,
                html: data.html,
                confirmButtonText: data.btnText,
                cancelButtonText: rss_ajax_obj.js_lang.Cancel,
                customClass: {
                    popup: 'swal-danger-container'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                },
                inputValue: 0,
                inputPlaceholder:
                rss_ajax_obj.js_lang.checkbox_delete_label,
            })
            return accept;
        }


        //Message Handle
        function success_message(msg) {
            let x = document.getElementById("snackbar-success");
            x.innerHTML = msg;
            x.className = "show";
            setTimeout(function () {
                x.className = x.className.replace("show", "");
            }, 3000);
        }

        function warning_message(msg) {
            let x = document.getElementById("snackbar-warning");
            x.innerHTML = msg;
            x.className = "show";
            setTimeout(function () {
                x.className = x.className.replace("show", "");
            }, 3000);
        }

    })(jQuery); // jQuery End
});