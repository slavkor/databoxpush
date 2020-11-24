const PushList = function () {

    this.init = function () {
        $('#data-table').DataTable({
            'processing': true,
            'serverSide': true,
            'language': {
                //  'url': __('js/datatable-english.json')
            },
            'ajax': {
                'url': 'push/list',
                'type': 'POST',
                'dataSrc': 'MyList'
            },
            'columns': [
                {'data': 'origin'},
                {'data': 'date'},
                {'data': 'metrics'},
                {'data': 'values'},
            ]
        });
    };

    this.init();
};

$(function () {
    new PushList();
});

