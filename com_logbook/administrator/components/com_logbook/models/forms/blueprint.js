jQuery(function() {
    document.formvalidator.setHandler('title',
        function (value) {
            regex=/^[a-zA-Z0-9 -#]+$/;
            return regex.test(value);
        });
});
