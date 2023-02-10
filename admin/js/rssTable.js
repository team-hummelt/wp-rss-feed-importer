let dataTableRssOverview

function rssTable() {

    dataTableRssOverview = new DataTable('#dataTablesRss', {
        "language": {
            "url": rss_ajax_obj.data_table
        },
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Alle"]],
        "pageLength": 10,
        "searching": true,
        "paging": true,
        "autoWidth": true,
        "columns": [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            {
                "width": "6%"
            },
            {
                "width": "6%"
            },
            {
                "width": "6%"
            },
        ],
        columnDefs: [{
            orderable: false,
            targets: [3, 5, 8, 9, 10]
        }, {
            targets: [0, 2, 3, 4],
            className: 'align-middle'
        }, {
            targets: [1, 5, 6, 7, 8, 9, 10],
            className: 'align-middle text-center'
        }
        ],
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: rss_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'RssImporter',
                _ajax_nonce: rss_ajax_obj.nonce,
                method: 'rss_table'
            },
           /* "dataSrc": function (json) {
                let timeWrapper = document.getElementById('tableNextCron');
                if (json.time_status) {
                    let endtime = new Date(json.next_time);
                        initializeClock('#tableNextCron', endtime);
                } else {
                    timeWrapper.innerHTML = '<span class="fw-semibold text-danger">nicht aktiv</span>';
                }
            }*/
        }
    });
}


/** ============================================================
 * ======================= COUNTDOWN UHR =======================
 * =============================================================*/
function getTimeRemaining(endtime) {
    const total = Date.parse(endtime) - Date.parse(new Date());
    const seconds = Math.floor((total / 1000) % 60);
    const minutes = Math.floor((total / 1000 / 60) % 60);
    const hours = Math.floor((total / (1000 * 60 * 60)) % 24);
    const days = Math.floor(total / (1000 * 60 * 60 * 24));

    return {
        total,
        days,
        hours,
        minutes,
        seconds
    };
}

function initializeClock(target, endtime) {

    if (!target) {
        return false;
    }
    const timeinterval = setInterval(() => {
        const t = getTimeRemaining(endtime);
        const clock = document.querySelector(target);
        if(!clock){
            return false;
        }
        clock.innerHTML = `<small class="fw-semibold mt-2">${t.days > 0 ? t.days + ' Day(s) ' : ''} ${('0' + t.hours).slice(-2)}:${('0' + t.minutes).slice(-2)}:${('0' + t.seconds).slice(-2)}</small>`;
        if (t.total <= 0) {
            clearInterval(timeinterval);
        }
    }, 1000);
}