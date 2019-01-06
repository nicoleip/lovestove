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
        oldQuantity = eval($(this).text());
        oldQuantity = parseFloat($(this).text());
        newQuantity = (oldQuantity * (newPeople/people)).toFixed(2);
        $(this).text(newQuantity);
      });

      $('.recipe__info-data--people').html(newPeople);
}

function likeRecipe()
{
    var recipeIdHash = window.location.hash;
    var recipe_id = recipeIdHash.replace('#', '');
    var recipe_title = $('#recipe-title').html();
    var recipe_source_url = $('#recipe-source-url').attr('href');
    var recipe_img_url = $('#recipe-img').attr('src');
    var recipe_publisher = $('#recipe-publisher').html();
    var recipe_time = $('#recipe-time').html();
    var recipe_servings = $('#recipe-servings').html();
    var recipe_ingredients_obj = {}
    var recipe_ingredients = [];
    $('.recipe__item').each(function(key, value){
        recipe_ingredients_obj = {
            "count": $('.recipe__count').html(),
            "unit": $('.recipe__unit').html(),
            "ingredient": $('.recipe__ingredient').html()
        }
        console.log(recipe_ingredients_obj);
        recipe_ingredients.push(recipe_ingredients_obj);
    });

    $.ajax({
        type: 'POST',
        url: '/saveRecipe',
        beforeSend: function(xhr){xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));},
        data: {
            'recipe_id': recipe_id,
            'recipe_title': recipe_title,
            'recipe_img_url': recipe_img_url,
            'recipe_publisher': recipe_publisher,
            'recipe_time': recipe_time,
            'recipe_servings': recipe_servings,
            'recipe_source_url': recipe_source_url,
            'recipe_ingredients': recipe_ingredients
        },
        success: function(response)
        {
          alert('Success');
        },
        error: function(response){
            alert('There was an error');
        }
    });
    
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


    // Download Shopping List
    $('body').on('click', '#print-btn', function(e) {
        var html = '<p>My shopping list</p><ul>';
        $('.shopping__description').each(function() {
            html += ('<li>');
            html += $(this).html();
            html += ('</li>');
        });
        html += ('</ul>');
        console.log(html);
        $.ajax({
            type: 'POST',
            url: '/printList',
            beforeSend: function(xhr){xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));},
            data: {
                'html': html
            },
            success: function(response)
            {
                var a = document.createElement('a');
                a.href= "data:application/pdf;base64,"+response;
                a.target = '_blank';
                a.download = 'my_shopping_list.pdf';
                a.click();
            },
            error: function(response){
                alert('There was an error');
            }
        });        
    })

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

    //Like Recipe
    $('body').on('click', '.recipe__details', function(e) {
        var btn = e.target.closest('.recipe__love');
        if(btn) {
            likeRecipe();
        }
    });

    //Populate Shopping List Area
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
                    $('.save-list').html('');
                    $('.shopping__list').append(response);
                    $('.save-list').append('<button id="print-btn" class="btn-small recipe__btn">Save shopping list</button>');
                }
                ,
                error: function(response){
                    alert('There was an error');
                }
            });        
             

        }
    });

    // Get Saved Recipes
    $('.likes__field').hover(function(e) {
        console.log('hovers');
            $.ajax({
                type: 'GET',
                url: '/getSavedRecipes',
                beforeSend: function(xhr){xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));},
                success: function(response)
                {
                    $('.likes__list').html('');
                    $('.likes__list').append(response);
                }
                ,
                error: function(response){
                    alert('There was an error');
                }
            });        
             

        
    });

});
