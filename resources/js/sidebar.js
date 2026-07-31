import $ from 'jquery';

$(function () {
    const $sidebar = $('.eh-sidebar');
    const $backdrop = $('.eh-sidebar-backdrop');

    $('.eh-sidebar-toggle').on('click', function () {
        $sidebar.toggleClass('show');
        $backdrop.toggleClass('show');
    });

    $backdrop.on('click', function () {
        $sidebar.removeClass('show');
        $backdrop.removeClass('show');
    });
});
