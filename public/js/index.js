function renderLoader()
{
    const loader = '<div class="loader"><svg><use href="img/icons.svg#icon-cw"></use></svg>';
    $('.results').prepend(loader);
}

function clearLoader()
{
    $('.loader').remove(); 
}

$(document).ready(function() {

    $('.search').on('submit', function(e){
        e.preventDefault();
    });
    
    $('.search__btn').on('click', function() {
        $('.results__list').html('');
        $('.results__pages').html('');
        renderLoader();
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
               console.log(response);
               $('.results__list').append(response.markup);
               $('.results__pages').append(response.buttons)
            },
            error: function(response){
                alert('There was an error');
            }
        });

    });

})
