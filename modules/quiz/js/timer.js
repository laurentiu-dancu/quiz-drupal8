/**
 * Created by laurentiu.dancu on 04.01.2016.
 */
(function ($, Drupal, drupalSettings) {
    "use strict";
    /**
     * Attaches the JS test behavior to weight div.
     */
    Drupal.behaviors.timer = {
        attach: function(context, settings) {
            var endtime = drupalSettings.quiz.endtime;
            var date = new Date(null);
            date.setSeconds(endtime);
            var newDiv = $('<div id="timer"></div>').html('Time remaining: ' + date.toISOString().substr(11, 8));
            $('#js-timer').append(newDiv);

            var counter=setInterval(timer, 1000); //1000 will  run it every 1 second

            function timer()
            {
                endtime=endtime -1 ;
                if (endtime < 0)
                {
                    clearInterval(counter);
                    location.reload();
                    //counter ended, do something here
                    return;
                }
                var date = new Date(null);
                date.setSeconds(endtime);
                document.getElementById("timer").innerHTML= 'Time remaining: ' + date.toISOString().substr(11, 8);
            }

        }
    };
})(jQuery, Drupal, drupalSettings);