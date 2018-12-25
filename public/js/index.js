$(document).ready(function() {

    $('.search').on('submit', function(e){
        e.preventDefault();
    });
    
    $('.search__btn').on('click', function() {
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
               $('.results__list').append(response);
            },
            error: function(response){
                alert('There was an error');
            }
        });

    });

})
