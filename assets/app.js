import "bootstrap"
import "bootstrap/dist/css/bootstrap.min.css"
import "bootstrap-icons/font/bootstrap-icons.min.css"
import "./styles/app.css"

;("use strict")

/* ── Theme toggle ───────────────────────────────────────────── */
function initTheme() {
    document.querySelectorAll(".theme-toggle [data-set-theme]").forEach((btn) => {
        btn.addEventListener("click", () => {
            const theme = btn.dataset.setTheme
            document.documentElement.setAttribute("data-theme", theme)
            localStorage.setItem("theme", theme)
        })
    })

    window.addEventListener("storage", (e) => {
        if (e.key === "theme" && e.newValue) {
            document.documentElement.setAttribute("data-theme", e.newValue)
        }
    })
}

initTheme()

const geocachesRetrieved = []
const fetchingUnpublishedCachesElm = document.getElementById("fetching-unpublished-caches")
const noGeocachesMessageElm = document.getElementById("no-geocaches-message")
const searchGeocodesElm = document.getElementById("search-geocodes")
const selectAllElm = document.getElementById("select-all")

searchGeocodesElm.addEventListener("click", () => {
    if (document.getElementById("geocodes").value === "") {
        return
    }

    fetchGeocodes(document.getElementById("geocodes").value)
})

const fetchUnpublishedGeocaches = () => {
    fetchingUnpublishedCachesElm.style.display = "block"
    // $("#create-gpx").button("reset")
    fetchGeocodes()
}

const fetchGeocodes = (geocodes) => {
    let payload = null

    if (typeof geocodes !== "undefined") {
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

const json = (response) => response.json()

const filterData = (data) => {
    data.forEach((g) => {
        if (
            Object.keys(geocachesRetrieved).find((key) => geocachesRetrieved[key].referenceCode === g.referenceCode) ===
            undefined
        ) {
            geocachesRetrieved.push(g)
        }
    })
}

const displayGeocaches = () => {
    selectAllElm.checked = false
    fetchingUnpublishedCachesElm.style.display = "none"

    if (geocachesRetrieved.length === 0) {
        noGeocachesMessageElm.style.display = "block"
        document.getElementById("table-unpublished-caches").style.display = "none"
        return
    }

    noGeocachesMessageElm.style.display = "none"
    document.getElementById("table-unpublished-caches").style.display = "block"
    document.querySelector("#table-caches tbody").innerHTML = ""
    geocachesRetrieved.forEach((g, index) => {
        document.querySelector("#table-caches tbody").insertAdjacentHTML(
            "beforeend",
            `<tr class="${g.referenceCode}" data-counter="${index + 1}" title="Add this geocache to the GPX">
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
                </tr>`
        )
    })

    document.getElementById("totalGeocaches").innerHTML = `(${geocachesRetrieved.length})`
}

document.getElementById("select-all").addEventListener("click", (e) =>
    document.querySelectorAll(".unpublished-geocache").forEach((elm) => {
        elm.checked = e.target.checked
    })
)

document.querySelector("#table-caches tbody").addEventListener("click", (e) => {
    if (!document.getElementById("chk_select").checked || !e.target.checked) {
        return
    }

    const countFrom = parseInt(e.target.parentElement.parentElement.dataset.counter, 10),
        countTo = parseInt(document.querySelector("#block_select input[type=range]").value, 10) - 1,
        geocacheToPick = document.querySelectorAll(
            "#table-caches tbody " +
                "tr:nth-child(n+" +
                (countFrom + 1) +
                "):nth-child(-n+" +
                (countFrom + countTo) +
                ") " +
                "input[type=checkbox]"
        )

    geocacheToPick.forEach((elm) => {
        elm.checked = true
    })
})

document
    .getElementById("chk_split")
    .addEventListener(
        "change",
        (e) => (document.querySelector("#block_split input[type=range]").disabled = !e.target.checked)
    )

document
    .getElementById("chk_select")
    .addEventListener(
        "change",
        (e) => (document.querySelector("#block_select input[type=range]").disabled = !e.target.checked)
    )

document
    .querySelector("#block_split input[type=range]")
    .addEventListener(
        "change",
        (e) =>
            (document.querySelector("label[for=chk_split]").innerHTML =
                `Split GPX files by ${e.target.value} geocaches`)
    )

document
    .querySelector("#block_select input[type=range]")
    .addEventListener(
        "change",
        (e) => (document.querySelector("label[for=chk_select]").innerHTML = `Pick ${e.target.value} geocaches`)
    )

document.getElementById("create-gpx").addEventListener("click", () => {
    const geocodes = []

    // let create = $(this)

    document.querySelectorAll("input[name=cache]:checked").forEach((e) => {
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

    const gpxSplit = document.getElementById("chk_split").checked
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
        .then((data) => {
            if (data?.success) {
                document.getElementById("download-links").innerHTML = data.links
                    .map(
                        (l) =>
                            `<li><a href="${l.href}" class="btn btn-success"><i class="bi bi-file-arrow-down"></i> ${l.label}</a></li>`
                    )
                    .join("")
            } else {
                alert(data.message)
            }
        })
        .catch((err) => {
            console.error(err)
        })
})

document.addEventListener("DOMContentLoaded", () => {
    fetchUnpublishedGeocaches()
})
