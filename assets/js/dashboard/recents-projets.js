import $ from 'jquery';

$(() => {
    if (!window['last_activities']) {
        return;
    }

    const createProjetPath = id => window['last_activities'].dataset.urlProjet.replace('0', id);

    fetch('/api/dashboard/recents-projets')
        .then(response => response.json())
        .then(({lastActivities}) => {
            if (0 === lastActivities.length) {
                return;
            }

            const $renderContent = $('#last_activities .activities-content');

            lastActivities.forEach(activity => {
                const projetPath = createProjetPath(activity.projetId);

                const $activity = $(`                                    
                    <div class="activity-item" data-filter-type="${activity.filterType}">
                        <div class="left-border" style="background-color: ${activity.colorCode};"></div>
                        <div>
                            <span><i>${activity.datetime}</i></span> | 
                            <a class="projet_acronyme" href="${projetPath}">${activity.acronyme}</a>
                        </div>
                        <div>
                          <ul class="list-unstyled">${activity.activity}</ul>
                        </div>
                    </div>
                `);

                $renderContent.append($activity);
            });
        });

    $(document).on('click', 'p[data-target-filter-type]', function (e) {
        let targetFilterType = e.target.dataset.targetFilterType;
        $('p[data-target-filter-type]').removeClass('active');
        $(this).addClass('active');

        if (targetFilterType){
            $('.activity-item').hide();
            $(`.activity-item[data-filter-type='${targetFilterType}']`).show();

        } else {
            $('.activity-item').show();
        }

        $("#last_activities .activities-content").animate({scrollTop:0});
    })
});

