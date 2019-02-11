(function ($) {
  "use strict";

  let fetchUnpublishedCaches = function () {
    // $('#refresh-cache').button('loading');
    $("#create-gpx").button("reset");

    $.ajax("unpublished.php")
      .done(function(data) {
        $("#select-all").prop("checked", false);
        $("#fetching-unpublished-caches").hide();
        // $('#refresh-cache').button('reset');
        if (!data || data === "" || typeof data !== "object") {
            // console.error(data);
            return false;
        }
        if (data && !data.success) {
            alert(data.message);
            // console.error(data.message);
        } else {
            $("#table-unpublished-caches").show();
            $("#table-caches tbody").html("");

            $.each(data.geocaches, function(index, g) {
                $("#table-caches tbody")
                    .append(`
                    <tr class="${g.referenceCode}" data-counter=" ${index+1}" title="Add this geocache to the GPX">
                            <td style="text-align: center;"><input type="checkbox" name="cache" class="unpublished-geocache" value="${g.referenceCode }" id="${g.referenceCode}" /></td>
                            <td>#${index+1}</td>
                            <td>${g.referenceCode}</td>
                            <td><label for="${g.referenceCode}"><img src="${g.geocacheType.imageUrl}" alt="${g.geocacheType.name}" width="24" /> ${g.name}</label></td>
                            <td class="link"><a href="${g.url}" title="View on geocaching.com"><span class="glyphicon glyphicon-new-window"></span></a></td>
                        </tr>`);
            });
            $("#totalGeocaches").html("(" + data.count + ")");

            $("#table-caches tbody").show();
        }
      })
      .fail(function(jqXHR, textStatus, errorThrown) {
        alert(textStatus);
        console.error(jqXHR, textStatus, errorThrown);
      });
  };

  $("#select-all").click(function () {
    $(".unpublished-geocache").prop("checked", $(this).is(":checked"));
  });

  $("#table-caches tbody").on("click", "input[type=checkbox]", function () {
    if ($("#chk_select").prop("checked") && $(this).prop("checked")) {
      var countFrom = parseInt($(this).parents("tr").data("counter"), 10);

      var countTo = parseInt($("#block_select input[type=range]").val(), 10) - 1;
      $("#table-caches tbody tr:nth-child(n+" + (countFrom + 1) + "):nth-child(-n+" + (countFrom + countTo) + ") input[type=checkbox]").prop("checked", true);
    }
  });
  // $('#refresh-cache').click(function() {
  //     $('#table-caches tbody').slideUp(400, fetchUnpublishedCaches);
  // });

  $("#chk_split").change(function () {
    $("#block_split input[type=range]").prop("disabled", !$(this).prop("checked"));
  });

  $("#block_split input[type=range]").change(function () {
    $("label[for=chk_split]").html("Split GPX files by " + $(this).val() + " geocaches");
  });

  $("#chk_select").change(function () {
    $("#block_select input[type=range]").prop("disabled", !$(this).prop("checked"));
  });

  $("#block_select input[type=range]").change(function () {
    $("label[for=chk_select]").html("Pick " + $(this).val() + " geocaches");
  });

  $("#create-gpx").click(function () {
    let geocodes = [];

    let create = $(this);

    $("input[name=cache]:checked").each(function () {
      geocodes.push(this.value);
    });

    if (geocodes.length <= 0) {
      alert("You must choose at least one cache.");
      return false;
    }

    $("#download-links").html("");
    $("#table-caches tbody tr").removeClass("success");
    $("#table-caches tbody tr").removeClass("danger");
    $("#table-caches .status").html("");

    create.button("loading");

    let gpxSplit = $("#chk_split").prop("checked") ? +$("#block_split input[type=range]").val() : 0;

    $.ajax({
      url: "geocaches.php",
      type: "POST",
      data: {
        "geocodes": geocodes,
        "gpxSplit": gpxSplit
      }
    }).done(function (data) {
      if (data && data.success) {
        // console.log(data.fail);
        $("#download-links").append(data.link);
      } else {
        alert(data.message);
        // console.error(data.message);
      }
    }).fail(function (jqXHR, textStatus) {
      alert(textStatus);
      // console.error(textStatus);
    }).always(function () {
      create.button("reset");
    });
  });

  $().ready(function() {
    if (user) {
      $("#fetching-unpublished-caches").show(0, fetchUnpublishedCaches);
    }
  });

}($));
