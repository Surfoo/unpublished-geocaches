import './styles/app.css'
import 'bootstrap'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.min.css'

"use strict"

let geocachesRetrieved = []
let fetchingUnpublishedCachesElm = document.getElementById('fetching-unpublished-caches')
let searchGeocodesElm = document.getElementById('search-geocodes')
let selectAllElm = document.getElementById('select-all')

searchGeocodesElm.addEventListener('click', function () {
  if (document.getElementById("geocodes").value === "") {
    return
  }

  fetchGeocodes(document.getElementById("geocodes").value)
})

let fetchUnpublishedGeocaches = function () {
  fetchingUnpublishedCachesElm.style.display = 'block'
  // $("#create-gpx").button("reset")
  fetchGeocodes()
}

let fetchGeocodes = function(geocodes) {
  let payload = null

  if (typeof geocodes !== 'undefined') {
    payload = JSON.stringify({
      geocodes: document.getElementById("geocodes").value,
    })
  }

  fetch("/unpublished", {
    method: "POST",
    mode: "same-origin",
    credentials: "same-origin",
    headers: {
      "Content-Type": "application/json",
    },
    body: payload,
  })
    .then(json)
    .then(filterData)
    .then(displayGeocaches)
    .catch((err) => {
      console.error(err)
    })
}

let json = function (response) {
  return response.json()
}

let filterData = function (data) {
  data.forEach((g) => {
    if (
      Object.keys(geocachesRetrieved).find(
        (key) => geocachesRetrieved[key].referenceCode === g.referenceCode
      ) === undefined
    ) {
      geocachesRetrieved.push(g)
    }
  })
}

let displayGeocaches = function () {
  selectAllElm.checked = false
  fetchingUnpublishedCachesElm.style.display = 'none'
  document.getElementById("table-unpublished-caches").style.display = 'block'
  document.querySelector("#table-caches tbody").innerHTML = ''
  geocachesRetrieved.forEach(function (g, index) {
    document.querySelector("#table-caches tbody").insertAdjacentHTML('beforeend',
            `<tr class="${
              g.referenceCode
            }" data-counter="${index + 1}" title="Add this geocache to the GPX">
                    <td style="text-align:center;"><input type="checkbox" name="cache" class="unpublished-geocache" value="${
                      g.referenceCode
                    }" id="${g.referenceCode}" /></td>
                    <td style="text-align:right;">#${index + 1}</td>
                    <td>${g.referenceCode}</td>
                    <td><label for="${
                      g.referenceCode
                    }"><img src="${g.geocacheType.imageUrl}" alt="${g.geocacheType.name}" width="24" /> ${g.name}</label></td>
                    <td class="link" style="text-align:center;"><a href="${
                      g.url
                    }" title="View on geocaching.com"><i class="bi bi-link-45deg"></i></a></td>
                </tr>`)
  })

  document.getElementById('totalGeocaches').innerHTML = "(" + geocachesRetrieved.length + ")"
}

document.getElementById('select-all').addEventListener('click', e =>
  document.querySelectorAll('.unpublished-geocache').forEach(elm => elm.checked = e.target.checked)
)

document.querySelector("#table-caches tbody").addEventListener('click', function(e) {
  if (!document.getElementById("chk_select").checked || !e.target.checked) {
    return
  }

  let countFrom = parseInt(e.target.parentElement.parentElement.dataset.counter, 10),
      countTo = parseInt(document.querySelector("#block_select input[type=range]").value, 10) - 1,
      geocacheToPick = document.querySelectorAll("#table-caches tbody " +
                          "tr:nth-child(n+" + (countFrom + 1) + "):nth-child(-n+" + (countFrom + countTo) +") "+
                          "input[type=checkbox]")

  geocacheToPick.forEach(elm => elm.checked = true)
})

document.getElementById("chk_split").addEventListener("change", e =>
  document.querySelector("#block_split input[type=range]").disabled = !e.target.checked
)

document.getElementById("chk_select").addEventListener('change', e =>
  document.querySelector("#block_select input[type=range]").disabled = !e.target.checked
)

document.querySelector("#block_split input[type=range]").addEventListener('change', e =>
  document.querySelector("label[for=chk_split]").innerHTML = `Split GPX files by ${e.target.value} geocaches`
)

document.querySelector("#block_select input[type=range]").addEventListener('change', e =>
  document.querySelector("label[for=chk_select]").innerHTML = `Pick ${e.target.value} geocaches`
)

document.getElementById("create-gpx").addEventListener('click', function () {
  let geocodes = []

  // let create = $(this)

  document.querySelectorAll("input[name=cache]:checked").forEach(function (e) {
    geocodes.push(e.value)
  })

  if (geocodes.length <= 0) {
    alert("You must choose at least one cache.")
    return
  }

  document.getElementById("download-links").innerHTML = ""
  document.querySelector("#table-caches tbody tr").classList.remove("success")
  document.querySelector("#table-caches tbody tr").classList.remove("danger")

  // create.button("loading")

  let gpxSplit = document.getElementById("chk_split").checked
    ? +document.querySelector("#block_split input[type=range]").value
    : 0

  fetch("/createGpx", {
    method: "POST",
    mode: "same-origin",
    credentials: "same-origin",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      geocodes: geocodes,
      gpxSplit: gpxSplit,
    }),
  })
    .then(json)
    .then(function(data) {
      if (data && data.success) {
        document.getElementById("download-links").innerHTML = data.link.join('')
      } else {
        alert(data.message)
      }
    })
    .catch((err) => {
      console.error(err)
    })
})

document.addEventListener("DOMContentLoaded", function() {
  fetchUnpublishedGeocaches()
})
