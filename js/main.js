// feedback form submit handle function
let feedback_form_submit = function(e) {
  // prevent submit
  e.preventDefault();

  // init data
  let $form = jQuery(e.target);
  let $wrapper = $form.closest('.codeable-block');

  let ajax_url = codeable_var.ajax_url;
  let form_data = $form.serialize();

  // remove error message and show loader
  $wrapper.removeClass('codeable-error').addClass('loading');

  // ajax feedback submit
  jQuery.ajax({
    url: ajax_url,
    method: 'POST',
    dataType: 'json',
    data: form_data,
    success: function(response) {
      if ( response.success ) {
        // handle success
        // remove form and show success message
        $wrapper.addClass('codeable-success');

        // scroll to top to show the message
        $wrapper.get(0).scrollIntoView({behavior: "smooth"});

      } else {
        // handle error
        // show error message
        $form.find('.error-message').html(response.data.message);
        $wrapper.addClass('codeable-error');
      }
    },
    error: function(xhr, ajaxOptions, thrownError) {
      // show error response text
      $form.find('.error-message').html(xhr.status + ' : ' + xhr.statusText);
      $wrapper.addClass('codeable-error');
    },
    complete: function() {
      // remove loader
      $wrapper.removeClass('loading');
    }

  });

}

jQuery(document).ready(function(){

  let ajax_url = codeable_var.ajax_url;

  // function to update feedback list by ajax request
  let feedback_list_load = function( $wrapper, page = 1, per_page = 10 ) {

    // remove error message and show loader
    $wrapper.removeClass('codeable-error').addClass('loading');

    // ajax pull list
    jQuery.ajax({
      url: ajax_url,
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'codeable_feedback_get_list',
        _nonce: $wrapper.attr('_nonce'),
        page,
        per_page
      },
      success: function(response) {
        if ( response.success ) {
          // handle success
          // render list
          render_feedback_list($wrapper, response.data.list);

          // render pagination
          render_pagination($wrapper, page, per_page, response.data.total_count);

        } else {
          // handle error
          $wrapper.find('.error-message').html(response.data.message);
          $wrapper.addClass('codeable-error');
        }
      },
      error: function(xhr, ajaxOptions, thrownError) {
        // show error response text
        $wrapper.find('.error-message').html(xhr.status + ' : ' + xhr.statusText);
        $wrapper.addClass('codeable-error');
      },
      complete: function() {
        // remove loader
        $wrapper.removeClass('loading');
      }

    });
  }

  // function to get feedback detail by ajax request
  let feedback_detail_load = function( $wrapper, feedback_id ) {
    $wrapper.removeClass('codeable-error').addClass('loading');

    // ajax pull list
    jQuery.ajax({
      url: ajax_url,
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'codeable_feedback_get_detail',
        _nonce: $wrapper.attr('_nonce'),
        id: feedback_id,
      },
      success: function(response) {
        if ( response.success ) {
          // render detail block
          render_feedback_detail($wrapper, response.data.item)

        } else {
          // handle error
          $wrapper.find('.error-message').html(response.data.message);
          $wrapper.addClass('codeable-error');
        }
      },
      error: function(xhr, ajaxOptions, thrownError) {
        // show error response text
        $wrapper.find('.error-message').html(xhr.status + ' : ' + xhr.statusText);
        $wrapper.addClass('codeable-error');
      },
      complete: function() {
        // remove loader
        $wrapper.removeClass('loading');
      }

    });
  }

  // function to render feedback list
  let render_feedback_list = function( $wrapper, feedback_items ) {
    if ( feedback_items.length ) {
      // hidden the empty message
      $wrapper.addClass('has-data');

      // add rows to list tbody
      let rows = '';
      for (item of feedback_items) {
        rows += `<tr data-id="${item.id}">
          <td>${item.first_name}</td>
          <td>${item.last_name}</td>
          <td>${item.email}</td>
          <td>${item.subject}</td>
        </tr>`;
      }
      $wrapper.find('tbody.feedback-list').html(rows);
    } else {
      // no items found
      // empty list
      $wrapper.find('tbody.feedback-list').html('');
      // show empty message
      $wrapper.removeClass('has-data');
    }
  }

  // pagination render function
  let render_pagination = function( $wrapper, page, per_page, total_count ) {

    if ( total_count <= per_page ) {
      // if just only one page available, hide pagination
      $wrapper.find('.codeable-pagination').hide();
    } else {
      // render pagination
      $wrapper.find('.codeable-pagination').show().pagination(
        {
          items: total_count,
          itemsOnPage: per_page,
          currentPage: page,
          displayedPages: 3,
          edges: 1,
          onPageClick(pageNumber, event) {
            // load new list
            feedback_list_load( $wrapper, pageNumber, per_page );
            // scroll to top
            $wrapper.get(0).scrollIntoView({behavior: "smooth"});
          }
        }
      );
    }
  }

  let render_feedback_detail = function( $wrapper, item ) {
    $wrapper.find('.txt-id').text(item.id);
    $wrapper.find('.txt-first-name').text(item.first_name);
    $wrapper.find('.txt-last-name').text(item.last_name);
    $wrapper.find('.txt-email').text(item.email);
    $wrapper.find('.txt-subject').text(item.subject);
    $wrapper.find('.txt-message').text(item.message);
  }

  // see details action
  jQuery('.feedback-list-table .feedback-list').on('click', 'tr', function(){
    let id = jQuery(this).data('id');
    let $wrapper = jQuery(this).closest('#feedback-list-wrapper').find('.feedback-detail-container');
    feedback_detail_load( $wrapper, id );
  });

  // update all feedback lists on document ready
  if ( jQuery('#feedback-list-wrapper').length ) {
    jQuery('#feedback-list-wrapper').each(function(index){
      feedback_list_load(jQuery(this).find('.feedback-list-container'), 1, 10 );
    })
  }
});