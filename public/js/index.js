function renderLoader(where)
{
    const loader = '<div class="loader"><svg><use href="img/icons.svg#icon-cw"></use></svg>';
    $(where).prepend(loader);
}

function clearLoader()
{
    $('.loader').remove();
}

function updateRecipe(type)
{
    people = parseInt($('.recipe__info-data--people').html());
    newPeople = type === 'dec' ? (people - 1) : (people + 1);
    console.log(newPeople);
    $(".recipe__count" ).each(function() {
        oldQuantity = parseFloat($(this).text());
        newQuantity = (oldQuantity * (newPeople/people)).toFixed(2);
        $(this).text(newQuantity);
      });

      $('.recipe__info-data--people').html(newPeople);
}

$(document).ready(function() {

    $('.search').on('submit', function(e){
        e.preventDefault();
    });
    
    //Populate search results panel with AJAX
    $('.search__btn').on('click', function(e) {      
        $('.results__list').html('');
        $('.results__pages').html('');
        renderLoader('.results');
        var query = $('.search__field').val();
        $.ajax({
            type: 'POST',
            url: '/getResults',
            beforeSend: function(xhr){xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));},
            data: {
                'search': query
            },
            success: function(response)
            {
               clearLoader();
               $('.results__list').html('');
               $('.results__pages').html('');
               $('.results__list').append(response.markup);
               $('.results__pages').append(response.buttons)
            },
            error: function(response){
                alert('There was an error');
            }
        });
        
    });

    $('.results__pages').on('click', function(e) {
        var btn = e.target.closest('.btn-inline');
        var query = $('.search__field').val();
        if(btn) {
            var gotoPage = parseInt($(btn).data('goto'));

            $.ajax({
                type: 'POST',
                url: '/getPaginatedResults',
                beforeSend: function(xhr){xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));},
                data: {
                    'search': query,
                    'page': gotoPage
                },
                success: function(response)
                {
                   $('.results__list').html('');
                   $('.results__pages').html('');
                   $('.results__list').append(response.markup);
                   $('.results__pages').append(response.buttons)
                },
                error: function(response){
                    alert('There was an error');
                }
            });        
        }
    });

    //Populate the recipe area with AJAX
    $('.results__list').on('click', function(e) {
        var recipeLink = e.target.closest('.results__link');
        if(recipeLink) {
            var recipeIdHash = $(recipeLink).attr('href');
            var recipeId = recipeIdHash.replace('#', '');
            $('.recipe').html('');
            renderLoader('.recipe');
            $.ajax({
                type: 'POST',
                url: '/getRecipe',
                beforeSend: function(xhr){xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));},
                data: {
                    'id': recipeId,
                },
                success: function(response)
                {
                    clearLoader();
                    $('.recipe').html('');
                    $('.recipe').append(response);
                }
                ,
                error: function(response){
                    alert('There was an error');
                }
            });        
        }
    });

    //Update Recipe Servings and Product Quantities
    $('body').on('click', '.recipe__info-buttons', function(e) {
        var btn = e.target.closest('.btn-tiny');
        if(btn) {
            console.log($(btn).data('value'));
            updateRecipe($(btn).data('value'));
        }
    });

    //Populate the recipe area with AJAX
    // $('.results__list').on('click', function(e) {
    //     var recipeLink = e.target.closest('.results__link');
    //     if(recipeLink) {
    //         var recipeIdHash = window.location.hash;
    //         var recipeId = recipeIdHash.replace('#', '');
    //         $('.recipe').html('');
    //         renderLoader('.recipe');
    //         $.ajax({
    //             type: 'POST',
    //             url: '/getRecipe',
    //             beforeSend: function(xhr){xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));},
    //             data: {
    //                 'id': recipeId,
    //             },
    //             success: function(response)
    //             {
    //                 clearLoader();
    //                 $('.recipe').html('');
    //                 $('.recipe').append(response);
    //             }
    //             ,
    //             error: function(response){
    //                 alert('There was an error');
    //             }
    //         });        
    //     }
    // });

    $('body').on('click', '.recipe__btn', function(e) {
        var btn = e.target.closest('.recipe__btn');
        if(btn) {
            var recipeIdHash = window.location.hash;
            var recipeId = recipeIdHash.replace('#', '');
            $('.shopping__list').html('');
            renderLoader('.shopping-loader');
            $.ajax({
                type: 'POST',
                url: '/getList',
                beforeSend: function(xhr){xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));},
                data: {
                    'id': recipeId,
                },
                success: function(response)
                {
                    clearLoader();
                    $('.shopping__list').html('');
                    $('.shopping__list').append(response);
                }
                ,
                error: function(response){
                    alert('There was an error');
                }
            });        
             

        }
    });
});
