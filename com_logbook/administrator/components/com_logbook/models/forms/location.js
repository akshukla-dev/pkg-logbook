jQuery(function() {
    document.formvalidator.setHandler('name',
        function (value) {
            regex=/^[a-zA-Z0-9 -#]+$/;
            return regex.test(value);
        });
});
