"use strict";
(self["webpackChunkunpublished_geocaches"] = self["webpackChunkunpublished_geocaches"] || []).push([["app"],{

/***/ "./app/app.js":
/*!********************!*\
  !*** ./app/app.js ***!
  \********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es_promise_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es.promise.js */ "./node_modules/core-js/modules/es.promise.js");
/* harmony import */ var core_js_modules_es_promise_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_promise_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var core_js_modules_es_regexp_exec_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! core-js/modules/es.regexp.exec.js */ "./node_modules/core-js/modules/es.regexp.exec.js");
/* harmony import */ var core_js_modules_es_regexp_exec_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_regexp_exec_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var core_js_modules_es_string_replace_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! core-js/modules/es.string.replace.js */ "./node_modules/core-js/modules/es.string.replace.js");
/* harmony import */ var core_js_modules_es_string_replace_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_string_replace_js__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var core_js_modules_es_array_find_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! core-js/modules/es.array.find.js */ "./node_modules/core-js/modules/es.array.find.js");
/* harmony import */ var core_js_modules_es_array_find_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_find_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var core_js_modules_es_object_keys_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! core-js/modules/es.object.keys.js */ "./node_modules/core-js/modules/es.object.keys.js");
/* harmony import */ var core_js_modules_es_object_keys_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_keys_js__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! core-js/modules/es.array.for-each.js */ "./node_modules/core-js/modules/es.array.for-each.js");
/* harmony import */ var core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var core_js_modules_es_array_concat_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! core-js/modules/es.array.concat.js */ "./node_modules/core-js/modules/es.array.concat.js");
/* harmony import */ var core_js_modules_es_array_concat_js__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_concat_js__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var core_js_modules_es_function_name_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! core-js/modules/es.function.name.js */ "./node_modules/core-js/modules/es.function.name.js");
/* harmony import */ var core_js_modules_es_function_name_js__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_function_name_js__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var core_js_modules_es_parse_int_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! core-js/modules/es.parse-int.js */ "./node_modules/core-js/modules/es.parse-int.js");
/* harmony import */ var core_js_modules_es_parse_int_js__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_parse_int_js__WEBPACK_IMPORTED_MODULE_9__);
/* harmony import */ var core_js_modules_es_string_link_js__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! core-js/modules/es.string.link.js */ "./node_modules/core-js/modules/es.string.link.js");
/* harmony import */ var core_js_modules_es_string_link_js__WEBPACK_IMPORTED_MODULE_10___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_string_link_js__WEBPACK_IMPORTED_MODULE_10__);
/* harmony import */ var _app_css__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./app.css */ "./app/app.css");
/* harmony import */ var bootstrap__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/npm.js");
/* harmony import */ var bootstrap__WEBPACK_IMPORTED_MODULE_12___default = /*#__PURE__*/__webpack_require__.n(bootstrap__WEBPACK_IMPORTED_MODULE_12__);















var geocachesRetrieved = [];

var fetchUnpublishedGeocaches = function fetchUnpublishedGeocaches() {
  $("#create-gpx").button("reset");
  fetch("unpublished.php", {
    mode: "same-origin",
    credentials: "same-origin"
  }).then(json).then(filterData).then(displayGeocaches)["catch"](function (err) {
    err.text().then(function (errorMessage) {
      console.error(errorMessage);
    });
  });
};

$("#search-geocodes").click(function () {
  if (document.getElementById("geocodes").value === "") {
    return false;
  }

  fetch("unpublished.php", {
    method: "POST",
    mode: "same-origin",
    credentials: "same-origin",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      geocodes: document.getElementById("geocodes").value
    })
  }).then(json).then(filterData).then(displayGeocaches)["catch"](function (err) {
    err.text().then(function (errorMessage) {
      console.error(errorMessage);
      alert(err.statusText);
    });
  });
});

var json = function json(response) {
  if (!response.ok) {
    if (response.status === 401) {
      window.location.replace(window.location.href + "?logout");
    }

    throw response;
  }

  return response.json();
};

var filterData = function filterData(data) {
  $.each(data.geocaches, function (index, g) {
    if (Object.keys(geocachesRetrieved).find(function (key) {
      return geocachesRetrieved[key].referenceCode === g.referenceCode;
    }) === undefined) {
      geocachesRetrieved.push(g);
    }
  });
};

var displayGeocaches = function displayGeocaches() {
  $("#select-all").prop("checked", false);
  $("#fetching-unpublished-caches").hide();
  $("#table-unpublished-caches").show();
  $("#table-caches tbody").html("");
  geocachesRetrieved.forEach(function (g, index) {
    $("#table-caches tbody").append("\n            <tr class=\"".concat(g.referenceCode, "\" data-counter=\" ").concat(index + 1, "\" title=\"Add this geocache to the GPX\">\n                    <td style=\"text-align: center;\"><input type=\"checkbox\" name=\"cache\" class=\"unpublished-geocache\" value=\"").concat(g.referenceCode, "\" id=\"").concat(g.referenceCode, "\" /></td>\n                    <td>#").concat(index + 1, "</td>\n                    <td>").concat(g.referenceCode, "</td>\n                    <td><label for=\"").concat(g.referenceCode, "\"><img src=\"").concat(g.geocacheType.imageUrl, "\" alt=\"").concat(g.geocacheType.name, "\" width=\"24\" /> ").concat(g.name, "</label></td>\n                    <td class=\"link\"><a href=\"").concat(g.url, "\" title=\"View on geocaching.com\"><span class=\"glyphicon glyphicon-new-window\"></span></a></td>\n                </tr>"));
  });
  $("#totalGeocaches").html("(" + geocachesRetrieved.length + ")");
  $("#table-caches tbody").show();
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
  var geocodes = [];
  var create = $(this);
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
  var gpxSplit = $("#chk_split").prop("checked") ? +$("#block_split input[type=range]").val() : 0;
  $.ajax({
    url: "geocaches.php",
    type: "POST",
    data: {
      geocodes: geocodes,
      gpxSplit: gpxSplit
    }
  }).done(function (data) {
    if (data && data.success) {
      // console.log(data.fail);
      $("#download-links").append(data.link);
    } else {
      alert(data.message); // console.error(data.message);
    }
  }).fail(function (jqXHR, textStatus) {
    alert(textStatus); // console.error(textStatus);
  }).always(function () {
    create.button("reset");
  });
});
$().ready(function () {
  if (user) {
    $("#fetching-unpublished-caches").show(0, fetchUnpublishedGeocaches);
  }
});

/***/ }),

/***/ "./app/app.css":
/*!*********************!*\
  !*** ./app/app.css ***!
  \*********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["vendors-node_modules_bootstrap_dist_js_npm_js-node_modules_core-js_modules_es_array_concat_js-d78e20"], () => (__webpack_exec__("./app/app.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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Q3VDLE9BQU4sQ0FBTCxDQURLLENBRUw7QUFDRDtBQUNGLEdBaEJILEVBaUJHQyxJQWpCSCxDQWlCUSxVQUFVQyxLQUFWLEVBQWlCQyxVQUFqQixFQUE2QjtBQUNqQ25ELElBQUFBLEtBQUssQ0FBQ21ELFVBQUQsQ0FBTCxDQURpQyxDQUVqQztBQUNELEdBcEJILEVBcUJHQyxNQXJCSCxDQXFCVSxZQUFZO0FBQ2xCWixJQUFBQSxNQUFNLENBQUMvRCxNQUFQLENBQWMsT0FBZDtBQUNELEdBdkJIO0FBd0JELENBakREO0FBbURBRCxDQUFDLEdBQUc2RSxLQUFKLENBQVUsWUFBWTtBQUNwQixNQUFJQyxJQUFKLEVBQVU7QUFDUjlFLElBQUFBLENBQUMsQ0FBQyw4QkFBRCxDQUFELENBQWtDK0MsSUFBbEMsQ0FBdUMsQ0FBdkMsRUFBMENoRCx5QkFBMUM7QUFDRDtBQUNGLENBSkQ7Ozs7Ozs7Ozs7O0FDck1BIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vdW5wdWJsaXNoZWQtZ2VvY2FjaGVzLy4vYXBwL2FwcC5qcyIsIndlYnBhY2s6Ly91bnB1Ymxpc2hlZC1nZW9jYWNoZXMvLi9hcHAvYXBwLmNzcz9jODA5Il0sInNvdXJjZXNDb250ZW50IjpbIlwidXNlIHN0cmljdFwiO1xuXG5pbXBvcnQgXCIuL2FwcC5jc3NcIjtcblxuaW1wb3J0IFwiYm9vdHN0cmFwXCI7XG5cbmxldCBnZW9jYWNoZXNSZXRyaWV2ZWQgPSBbXTtcblxubGV0IGZldGNoVW5wdWJsaXNoZWRHZW9jYWNoZXMgPSBmdW5jdGlvbiAoKSB7XG4gICQoXCIjY3JlYXRlLWdweFwiKS5idXR0b24oXCJyZXNldFwiKTtcblxuICBmZXRjaChcInVucHVibGlzaGVkLnBocFwiLCB7XG4gICAgbW9kZTogXCJzYW1lLW9yaWdpblwiLFxuICAgIGNyZWRlbnRpYWxzOiBcInNhbWUtb3JpZ2luXCIsXG4gIH0pXG4gICAgLnRoZW4oanNvbilcbiAgICAudGhlbihmaWx0ZXJEYXRhKVxuICAgIC50aGVuKGRpc3BsYXlHZW9jYWNoZXMpXG4gICAgLmNhdGNoKChlcnIpID0+IHtcbiAgICAgIGVyci50ZXh0KCkudGhlbigoZXJyb3JNZXNzYWdlKSA9PiB7XG4gICAgICAgIGNvbnNvbGUuZXJyb3IoZXJyb3JNZXNzYWdlKTtcbiAgICAgIH0pO1xuICAgIH0pO1xufTtcblxuJChcIiNzZWFyY2gtZ2VvY29kZXNcIikuY2xpY2soZnVuY3Rpb24gKCkge1xuICBpZiAoZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoXCJnZW9jb2Rlc1wiKS52YWx1ZSA9PT0gXCJcIikge1xuICAgIHJldHVybiBmYWxzZTtcbiAgfVxuXG4gIGZldGNoKFwidW5wdWJsaXNoZWQucGhwXCIsIHtcbiAgICBtZXRob2Q6IFwiUE9TVFwiLFxuICAgIG1vZGU6IFwic2FtZS1vcmlnaW5cIixcbiAgICBjcmVkZW50aWFsczogXCJzYW1lLW9yaWdpblwiLFxuICAgIGhlYWRlcnM6IHtcbiAgICAgIFwiQ29udGVudC1UeXBlXCI6IFwiYXBwbGljYXRpb24vanNvblwiLFxuICAgIH0sXG4gICAgYm9keTogSlNPTi5zdHJpbmdpZnkoe1xuICAgICAgZ2VvY29kZXM6IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKFwiZ2VvY29kZXNcIikudmFsdWUsXG4gICAgfSksXG4gIH0pXG4gICAgLnRoZW4oanNvbilcbiAgICAudGhlbihmaWx0ZXJEYXRhKVxuICAgIC50aGVuKGRpc3BsYXlHZW9jYWNoZXMpXG4gICAgLmNhdGNoKChlcnIpID0+IHtcbiAgICAgIGVyci50ZXh0KCkudGhlbigoZXJyb3JNZXNzYWdlKSA9PiB7XG4gICAgICAgIGNvbnNvbGUuZXJyb3IoZXJyb3JNZXNzYWdlKTtcbiAgICAgICAgYWxlcnQoZXJyLnN0YXR1c1RleHQpO1xuICAgICAgfSk7XG4gICAgfSk7XG59KTtcblxubGV0IGpzb24gPSBmdW5jdGlvbiAocmVzcG9uc2UpIHtcbiAgaWYgKCFyZXNwb25zZS5vaykge1xuICAgIGlmIChyZXNwb25zZS5zdGF0dXMgPT09IDQwMSkge1xuICAgICAgd2luZG93LmxvY2F0aW9uLnJlcGxhY2Uod2luZG93LmxvY2F0aW9uLmhyZWYgKyBcIj9sb2dvdXRcIik7XG4gICAgfVxuICAgIHRocm93IHJlc3BvbnNlO1xuICB9XG4gIHJldHVybiByZXNwb25zZS5qc29uKCk7XG59O1xuXG5sZXQgZmlsdGVyRGF0YSA9IGZ1bmN0aW9uIChkYXRhKSB7XG4gICQuZWFjaChkYXRhLmdlb2NhY2hlcywgZnVuY3Rpb24gKGluZGV4LCBnKSB7XG4gICAgaWYgKFxuICAgICAgT2JqZWN0LmtleXMoZ2VvY2FjaGVzUmV0cmlldmVkKS5maW5kKFxuICAgICAgICAoa2V5KSA9PiBnZW9jYWNoZXNSZXRyaWV2ZWRba2V5XS5yZWZlcmVuY2VDb2RlID09PSBnLnJlZmVyZW5jZUNvZGVcbiAgICAgICkgPT09IHVuZGVmaW5lZFxuICAgICkge1xuICAgICAgZ2VvY2FjaGVzUmV0cmlldmVkLnB1c2goZyk7XG4gICAgfVxuICB9KTtcbn07XG5cbmxldCBkaXNwbGF5R2VvY2FjaGVzID0gZnVuY3Rpb24gKCkge1xuICAkKFwiI3NlbGVjdC1hbGxcIikucHJvcChcImNoZWNrZWRcIiwgZmFsc2UpO1xuICAkKFwiI2ZldGNoaW5nLXVucHVibGlzaGVkLWNhY2hlc1wiKS5oaWRlKCk7XG4gICQoXCIjdGFibGUtdW5wdWJsaXNoZWQtY2FjaGVzXCIpLnNob3coKTtcbiAgJChcIiN0YWJsZS1jYWNoZXMgdGJvZHlcIikuaHRtbChcIlwiKTtcblxuICBnZW9jYWNoZXNSZXRyaWV2ZWQuZm9yRWFjaChmdW5jdGlvbiAoZywgaW5kZXgpIHtcbiAgICAkKFwiI3RhYmxlLWNhY2hlcyB0Ym9keVwiKS5hcHBlbmQoYFxuICAgICAgICAgICAgPHRyIGNsYXNzPVwiJHtcbiAgICAgICAgICAgICAgZy5yZWZlcmVuY2VDb2RlXG4gICAgICAgICAgICB9XCIgZGF0YS1jb3VudGVyPVwiICR7aW5kZXggKyAxfVwiIHRpdGxlPVwiQWRkIHRoaXMgZ2VvY2FjaGUgdG8gdGhlIEdQWFwiPlxuICAgICAgICAgICAgICAgICAgICA8dGQgc3R5bGU9XCJ0ZXh0LWFsaWduOiBjZW50ZXI7XCI+PGlucHV0IHR5cGU9XCJjaGVja2JveFwiIG5hbWU9XCJjYWNoZVwiIGNsYXNzPVwidW5wdWJsaXNoZWQtZ2VvY2FjaGVcIiB2YWx1ZT1cIiR7XG4gICAgICAgICAgICAgICAgICAgICAgZy5yZWZlcmVuY2VDb2RlXG4gICAgICAgICAgICAgICAgICAgIH1cIiBpZD1cIiR7Zy5yZWZlcmVuY2VDb2RlfVwiIC8+PC90ZD5cbiAgICAgICAgICAgICAgICAgICAgPHRkPiMke2luZGV4ICsgMX08L3RkPlxuICAgICAgICAgICAgICAgICAgICA8dGQ+JHtnLnJlZmVyZW5jZUNvZGV9PC90ZD5cbiAgICAgICAgICAgICAgICAgICAgPHRkPjxsYWJlbCBmb3I9XCIke1xuICAgICAgICAgICAgICAgICAgICAgIGcucmVmZXJlbmNlQ29kZVxuICAgICAgICAgICAgICAgICAgICB9XCI+PGltZyBzcmM9XCIke2cuZ2VvY2FjaGVUeXBlLmltYWdlVXJsfVwiIGFsdD1cIiR7Zy5nZW9jYWNoZVR5cGUubmFtZX1cIiB3aWR0aD1cIjI0XCIgLz4gJHtnLm5hbWV9PC9sYWJlbD48L3RkPlxuICAgICAgICAgICAgICAgICAgICA8dGQgY2xhc3M9XCJsaW5rXCI+PGEgaHJlZj1cIiR7XG4gICAgICAgICAgICAgICAgICAgICAgZy51cmxcbiAgICAgICAgICAgICAgICAgICAgfVwiIHRpdGxlPVwiVmlldyBvbiBnZW9jYWNoaW5nLmNvbVwiPjxzcGFuIGNsYXNzPVwiZ2x5cGhpY29uIGdseXBoaWNvbi1uZXctd2luZG93XCI+PC9zcGFuPjwvYT48L3RkPlxuICAgICAgICAgICAgICAgIDwvdHI+YCk7XG4gIH0pO1xuXG4gICQoXCIjdG90YWxHZW9jYWNoZXNcIikuaHRtbChcIihcIiArIGdlb2NhY2hlc1JldHJpZXZlZC5sZW5ndGggKyBcIilcIik7XG4gICQoXCIjdGFibGUtY2FjaGVzIHRib2R5XCIpLnNob3coKTtcbn07XG5cbiQoXCIjc2VsZWN0LWFsbFwiKS5jbGljayhmdW5jdGlvbiAoKSB7XG4gICQoXCIudW5wdWJsaXNoZWQtZ2VvY2FjaGVcIikucHJvcChcImNoZWNrZWRcIiwgJCh0aGlzKS5pcyhcIjpjaGVja2VkXCIpKTtcbn0pO1xuXG4kKFwiI3RhYmxlLWNhY2hlcyB0Ym9keVwiKS5vbihcImNsaWNrXCIsIFwiaW5wdXRbdHlwZT1jaGVja2JveF1cIiwgZnVuY3Rpb24gKCkge1xuICBpZiAoJChcIiNjaGtfc2VsZWN0XCIpLnByb3AoXCJjaGVja2VkXCIpICYmICQodGhpcykucHJvcChcImNoZWNrZWRcIikpIHtcbiAgICB2YXIgY291bnRGcm9tID0gcGFyc2VJbnQoJCh0aGlzKS5wYXJlbnRzKFwidHJcIikuZGF0YShcImNvdW50ZXJcIiksIDEwKTtcblxuICAgIHZhciBjb3VudFRvID0gcGFyc2VJbnQoJChcIiNibG9ja19zZWxlY3QgaW5wdXRbdHlwZT1yYW5nZV1cIikudmFsKCksIDEwKSAtIDE7XG4gICAgJChcbiAgICAgIFwiI3RhYmxlLWNhY2hlcyB0Ym9keSB0cjpudGgtY2hpbGQobitcIiArXG4gICAgICAgIChjb3VudEZyb20gKyAxKSArXG4gICAgICAgIFwiKTpudGgtY2hpbGQoLW4rXCIgK1xuICAgICAgICAoY291bnRGcm9tICsgY291bnRUbykgK1xuICAgICAgICBcIikgaW5wdXRbdHlwZT1jaGVja2JveF1cIlxuICAgICkucHJvcChcImNoZWNrZWRcIiwgdHJ1ZSk7XG4gIH1cbn0pO1xuXG4kKFwiI2Noa19zcGxpdFwiKS5jaGFuZ2UoZnVuY3Rpb24gKCkge1xuICAkKFwiI2Jsb2NrX3NwbGl0IGlucHV0W3R5cGU9cmFuZ2VdXCIpLnByb3AoXG4gICAgXCJkaXNhYmxlZFwiLFxuICAgICEkKHRoaXMpLnByb3AoXCJjaGVja2VkXCIpXG4gICk7XG59KTtcblxuJChcIiNibG9ja19zcGxpdCBpbnB1dFt0eXBlPXJhbmdlXVwiKS5jaGFuZ2UoZnVuY3Rpb24gKCkge1xuICAkKFwibGFiZWxbZm9yPWNoa19zcGxpdF1cIikuaHRtbChcbiAgICBcIlNwbGl0IEdQWCBmaWxlcyBieSBcIiArICQodGhpcykudmFsKCkgKyBcIiBnZW9jYWNoZXNcIlxuICApO1xufSk7XG5cbiQoXCIjY2hrX3NlbGVjdFwiKS5jaGFuZ2UoZnVuY3Rpb24gKCkge1xuICAkKFwiI2Jsb2NrX3NlbGVjdCBpbnB1dFt0eXBlPXJhbmdlXVwiKS5wcm9wKFxuICAgIFwiZGlzYWJsZWRcIixcbiAgICAhJCh0aGlzKS5wcm9wKFwiY2hlY2tlZFwiKVxuICApO1xufSk7XG5cbiQoXCIjYmxvY2tfc2VsZWN0IGlucHV0W3R5cGU9cmFuZ2VdXCIpLmNoYW5nZShmdW5jdGlvbiAoKSB7XG4gICQoXCJsYWJlbFtmb3I9Y2hrX3NlbGVjdF1cIikuaHRtbChcIlBpY2sgXCIgKyAkKHRoaXMpLnZhbCgpICsgXCIgZ2VvY2FjaGVzXCIpO1xufSk7XG5cbiQoXCIjY3JlYXRlLWdweFwiKS5jbGljayhmdW5jdGlvbiAoKSB7XG4gIGxldCBnZW9jb2RlcyA9IFtdO1xuXG4gIGxldCBjcmVhdGUgPSAkKHRoaXMpO1xuXG4gICQoXCJpbnB1dFtuYW1lPWNhY2hlXTpjaGVja2VkXCIpLmVhY2goZnVuY3Rpb24gKCkge1xuICAgIGdlb2NvZGVzLnB1c2godGhpcy52YWx1ZSk7XG4gIH0pO1xuXG4gIGlmIChnZW9jb2Rlcy5sZW5ndGggPD0gMCkge1xuICAgIGFsZXJ0KFwiWW91IG11c3QgY2hvb3NlIGF0IGxlYXN0IG9uZSBjYWNoZS5cIik7XG4gICAgcmV0dXJuIGZhbHNlO1xuICB9XG5cbiAgJChcIiNkb3dubG9hZC1saW5rc1wiKS5odG1sKFwiXCIpO1xuICAkKFwiI3RhYmxlLWNhY2hlcyB0Ym9keSB0clwiKS5yZW1vdmVDbGFzcyhcInN1Y2Nlc3NcIik7XG4gICQoXCIjdGFibGUtY2FjaGVzIHRib2R5IHRyXCIpLnJlbW92ZUNsYXNzKFwiZGFuZ2VyXCIpO1xuICAkKFwiI3RhYmxlLWNhY2hlcyAuc3RhdHVzXCIpLmh0bWwoXCJcIik7XG5cbiAgY3JlYXRlLmJ1dHRvbihcImxvYWRpbmdcIik7XG5cbiAgbGV0IGdweFNwbGl0ID0gJChcIiNjaGtfc3BsaXRcIikucHJvcChcImNoZWNrZWRcIilcbiAgICA/ICskKFwiI2Jsb2NrX3NwbGl0IGlucHV0W3R5cGU9cmFuZ2VdXCIpLnZhbCgpXG4gICAgOiAwO1xuXG4gICQuYWpheCh7XG4gICAgdXJsOiBcImdlb2NhY2hlcy5waHBcIixcbiAgICB0eXBlOiBcIlBPU1RcIixcbiAgICBkYXRhOiB7XG4gICAgICBnZW9jb2RlczogZ2VvY29kZXMsXG4gICAgICBncHhTcGxpdDogZ3B4U3BsaXQsXG4gICAgfSxcbiAgfSlcbiAgICAuZG9uZShmdW5jdGlvbiAoZGF0YSkge1xuICAgICAgaWYgKGRhdGEgJiYgZGF0YS5zdWNjZXNzKSB7XG4gICAgICAgIC8vIGNvbnNvbGUubG9nKGRhdGEuZmFpbCk7XG4gICAgICAgICQoXCIjZG93bmxvYWQtbGlua3NcIikuYXBwZW5kKGRhdGEubGluayk7XG4gICAgICB9IGVsc2Uge1xuICAgICAgICBhbGVydChkYXRhLm1lc3NhZ2UpO1xuICAgICAgICAvLyBjb25zb2xlLmVycm9yKGRhdGEubWVzc2FnZSk7XG4gICAgICB9XG4gICAgfSlcbiAgICAuZmFpbChmdW5jdGlvbiAoanFYSFIsIHRleHRTdGF0dXMpIHtcbiAgICAgIGFsZXJ0KHRleHRTdGF0dXMpO1xuICAgICAgLy8gY29uc29sZS5lcnJvcih0ZXh0U3RhdHVzKTtcbiAgICB9KVxuICAgIC5hbHdheXMoZnVuY3Rpb24gKCkge1xuICAgICAgY3JlYXRlLmJ1dHRvbihcInJlc2V0XCIpO1xuICAgIH0pO1xufSk7XG5cbiQoKS5yZWFkeShmdW5jdGlvbiAoKSB7XG4gIGlmICh1c2VyKSB7XG4gICAgJChcIiNmZXRjaGluZy11bnB1Ymxpc2hlZC1jYWNoZXNcIikuc2hvdygwLCBmZXRjaFVucHVibGlzaGVkR2VvY2FjaGVzKTtcbiAgfVxufSk7XG4iLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiXSwibmFtZXMiOlsiZ2VvY2FjaGVzUmV0cmlldmVkIiwiZmV0Y2hVbnB1Ymxpc2hlZEdlb2NhY2hlcyIsIiQiLCJidXR0b24iLCJmZXRjaCIsIm1vZGUiLCJjcmVkZW50aWFscyIsInRoZW4iLCJqc29uIiwiZmlsdGVyRGF0YSIsImRpc3BsYXlHZW9jYWNoZXMiLCJlcnIiLCJ0ZXh0IiwiZXJyb3JNZXNzYWdlIiwiY29uc29sZSIsImVycm9yIiwiY2xpY2siLCJkb2N1bWVudCIsImdldEVsZW1lbnRCeUlkIiwidmFsdWUiLCJtZXRob2QiLCJoZWFkZXJzIiwiYm9keSIsIkpTT04iLCJzdHJpbmdpZnkiLCJnZW9jb2RlcyIsImFsZXJ0Iiwic3RhdHVzVGV4dCIsInJlc3BvbnNlIiwib2siLCJzdGF0dXMiLCJ3aW5kb3ciLCJsb2NhdGlvbiIsInJlcGxhY2UiLCJocmVmIiwiZGF0YSIsImVhY2giLCJnZW9jYWNoZXMiLCJpbmRleCIsImciLCJPYmplY3QiLCJrZXlzIiwiZmluZCIsImtleSIsInJlZmVyZW5jZUNvZGUiLCJ1bmRlZmluZWQiLCJwdXNoIiwicHJvcCIsImhpZGUiLCJzaG93IiwiaHRtbCIsImZvckVhY2giLCJhcHBlbmQiLCJnZW9jYWNoZVR5cGUiLCJpbWFnZVVybCIsIm5hbWUiLCJ1cmwiLCJsZW5ndGgiLCJpcyIsIm9uIiwiY291bnRGcm9tIiwicGFyc2VJbnQiLCJwYXJlbnRzIiwiY291bnRUbyIsInZhbCIsImNoYW5nZSIsImNyZWF0ZSIsInJlbW92ZUNsYXNzIiwiZ3B4U3BsaXQiLCJhamF4IiwidHlwZSIsImRvbmUiLCJzdWNjZXNzIiwibGluayIsIm1lc3NhZ2UiLCJmYWlsIiwianFYSFIiLCJ0ZXh0U3RhdHVzIiwiYWx3YXlzIiwicmVhZHkiLCJ1c2VyIl0sInNvdXJjZVJvb3QiOiIifQ==