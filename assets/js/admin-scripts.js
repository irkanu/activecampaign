jQuery(document).ready(function ($) {
    /**
     * ac_vars
     */


    /**
     * Easily extract a query arg.
     *
     * @param variable
     * @returns {*}
     */
    function getQueryVariable(variable) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i=0;i<vars.length;i++) {
            var pair = vars[i].split("=");
            if(pair[0] == variable){return pair[1];}
        }
        return(false);
    }

    // Code to run on the General tab. Or ac-settings page as a fall back.
    if ( getQueryVariable('tab') == 'general' || getQueryVariable('page') == 'ac-settings' ) {

        // Check if the api settings are locked.
        if ( ac_vars.api_lock != null ) {
            // Disable them if they are
            $('#ac_settings_api_url').addClass('ac-disabled');
            $('#ac_settings_api_key').addClass('ac-disabled');
        }

        // Check if the api credentials are valid
        if ( ac_vars.api_cred_test == 'valid' ) {
            // Change the input background to green to let the user know they are valid.
            $('#ac_settings_api_url').addClass('ac-valid');
            $('#ac_settings_api_key').addClass('ac-valid');
        } else if ( ac_vars.api_cred_test == 'invalid' ) {
            // Change the input background to red to let the user know they are invalid.
            $('#ac_settings_api_url').addClass('ac-invalid');
            $('#ac_settings_api_key').addClass('ac-invalid');
        }
    }

    // Code to run on the Contacts tab.
    if ( getQueryVariable('tab') == 'contacts' ) {

        // Check to see if we've already tried to sync.
        if ( getQueryVariable('ac_action') != 'maybe_contact_sync' ) {
            var dots = 0;

            setInterval(function() {
                $('a[title="Sync Contacts "]').click(function() {
                    $(this).addClass('disabled').html('Syncing in progress<span id="dots"></span>');
                });
                if(dots < 5) {
                    $('#dots').append('.');
                    dots++;
                } else {
                    $('#dots').html('');
                    dots = 0;
                }
            }, 600);
        } else {
            $('a[title="Sync Contacts "]').addClass('ac-disabled').html('Done syncing!');
        }

    }

});
