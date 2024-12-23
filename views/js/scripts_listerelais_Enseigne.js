/**
 * 1969-2018 Relais Colis
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@relaiscolis.com so we can send you a copy immediately.
 *
 *  @author    Quadra Informatique <modules@quadra-informatique.fr>
 *  @copyright 1969-2018 Relais Colis
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

var myMap = null;
var POIs = null;
//Default country.
var mycountryIsoCode = "FRA";
//var relaisColisKey = "170446";
//number of delivery point research
var nbRelaisColis = 10;
//Code "enseigne" with special char
var ensCode = "RCñ";
var relaisCountry;
var locRel;
var rayonRecherche = 100000;
var delaiLivJour = 5;
if (typeof url_img !== 'undefined' && url_img) {
    url_img = url_img + 'views/img/';
    var lgRelaisColisOuvert = url_img + "pointer_on.png";
    var lgRelaisColisMaxOuvert = url_img + "pointer_max_on.png";
    var lgRelaisColisFerme = url_img + "pointer_off.png";
    var lgRelaisColisMaxFerme = url_img + "pointer_max_off.png";
    var lgRelaisColisNew = url_img + "pointer_new_on.png";
    var lgVousEtesIci = url_img + "VousEtesIci.gif";
    var lgTransp = url_img + "transp.gif";
}
//offset Y
var offsetY = -16;
//offset X
var offsetX = -16;

var critereiId = 1;
var critereVal = 1;
var critRCMaxiId = 2;
var critRCMaxVal = 1;

var first = true; // we-plus

if (typeof is_relais_max !== 'undefined' && is_relais_max) {
    var rcmax = is_relais_max;
}
if (typeof use_id_ens !== 'undefined' && use_id_ens) {
    ensCode = use_id_ens;
}

function toggle(obj) {
    var el = document.getElementById(obj);
    if (el.style.display != 'none') {
        el.style.display = 'none';
    }
    else {
        el.style.display = '';
    }
}

function geoCoder() {
    mycountryIsoCode = getCountryIsoCode();
    VMLaunch("ViaMichelin.Api.Geocoding", {
        address: document.getElementById("form_address").value,
        zip: document.getElementById("form_CP").value,
        city: document.getElementById("form_city").value,
        countryISOCode: mycountryIsoCode
    }, {
        onSuccess: function (results) {
            var out = '';
            var coordXY = '';
            switch (results.length) {
                case 0:
                    document.getElementById("divEmplacement").style.display = "none";
                    alert('Whoops : adresse non trouvée');
                    break;
                case 1:
                    document.getElementById("divEmplacement").style.display = "none";
                    searchPOIsByPoint(results[0].coords);
                    break;
                default:
                    document.getElementById("divEmplacement").style.display = "";
                    $('#selListeAdresses option').remove();
                    for (i = 0; i < results.length; ++i) {
                        if (results[i].formattedAddressLine) {
                            out += results[i].formattedAddressLine + ' - ';
                        }
                        if (results[i].formattedCityLine) {
                            out += results[i].formattedCityLine + '-';
                            out += '(' + results[i].coords.lat + ', ' + results[i].coords.lon + ')';
                            coordXY = '(' + results[i].coords.lat + ', ' + results[i].coords.lon + ')';
                            $('#selListeAdresses').append(new Option(out, i));
                            out = '';
                        }
                    }
                    resolveAmbiguity();
                    break;
            }
        },
        onError: function (error) {
            alert('Whoops' + error);
        }
    });

}

function launchResearch() {
    // need to refresh the map after carrier reselect ?
    var refresh = $('#refresh').val();
    if (refresh == 0) {
        myMap = null;
    }
    $('div .flag-img').empty();
    $('div .flag-img').append("<img id='loader_gif' src='" + url_img + "loader.gif' />");
    $('#map_rc_wrapper').show();
    if (null == myMap) {
        VMLaunch("ViaMichelin.Api.Map", {
            container: $_id("divMapContainer"),
            zoom: 11, //Initial zoom level
            mapTypeControlOptions: { type: ViaMichelin.Api.Constants.Map.TYPE.LIGHT },
            center: ViaMichelin.Api.Constants.Map.DELAY_LOADING
        }, {
            onInit: function (serviceMap) {
                myMap = serviceMap;
            },
            onSuccess: function () {
                $('div .flag-img').empty();
                $('div .flag-img').append("<img class='logo-relais' src='" + url_img + "rc_long_logo.png' />");
                $('#refresh').val('1');
                geoCoder();
            }
        });
    } else {
        $('div .flag-img').empty();
        $('div .flag-img').append("<img class='logo-relais' src='" + url_img + "rc_long_logo.png' />");
        geoCoder();
    }
}

function getCountryIsoCode() {
    var IsoCode = $("#list_country_rc option:selected").val();
    if (!IsoCode) {
        IsoCode = "FRA";
    }
    return IsoCode;
}

function getRelaisCountry() {
    var rCountry;
    locRel = getURLParam2("flocrel").toUpperCase();

    switch (locRel) {
        case "BEL":
            rCountry = ['BEL'];
            break;
        case "MCO":
            rCountry = ['MCO'];
            break;
        case "FRA":
            rCountry = ['FRA'];
            break;
        default:
            rCountry = "";
            break;
    }
    return rCountry;
}

function showTarget(point) {
    var marker = new ViaMichelin.Api.Map.Marker({
        coords: { lon: point.lon, lat: point.lat },
        title: 'Vous êtes ici',
        icon: { url: lgVousEtesIci, offset: { x: offsetX, y: offsetY } }
    });
    myMap.addLayer(marker);
}

function searchPOIsByPoint(point) {
    myMap.removeAllLayers();
    showTarget(point);
    POIs = null;
    relaisCountry = getRelaisCountry();
    if (rcmax == "1") {
        VMLaunch
            (
                "ViaMichelin.Api.Poi",
                {
                    service: ViaMichelin.Api.Constants.Poi.SERVICE_TYPE.FIND_POI,
                    db: relaisColisKey, //Database ID
                    dist: rayonRecherche,
                    countryLst: relaisCountry,
                    center: point, //Search center
                    nb: nbRelaisColis, //Number of returned POIs
                    text: ensCode,
                    criteria:
                        [{ id: critereiId, value: critereVal }, { id: critRCMaxiId, value: critRCMaxVal }],
                    map:
                    {
                        container: $_id("divMapContainer"),
                        offset: { x: offsetX + 30, y: offsetY + 30 },
                        iconPath: lgTransp
                    }
                },
                {
                    onSuccess: function (results) {
                        AfficherInfosRelais(results);
                    },
                    onError: function (error) {
                        alert('Whoops' + error);
                    }
                });
    } else {

        VMLaunch
            (
                "ViaMichelin.Api.Poi",
                {
                    service: ViaMichelin.Api.Constants.Poi.SERVICE_TYPE.FIND_POI,
                    db: relaisColisKey, //Database ID
                    dist: rayonRecherche,
                    countryLst: relaisCountry,
                    center: point, //Search center
                    nb: nbRelaisColis, //Number of returned POIs
                    text: ensCode,
                    criteria:
                        [{ id: critereiId, value: critereVal }],
                    map:
                    {
                        container: $_id("divMapContainer"),
                        offset: { x: offsetX + 30, y: offsetY + 30 },
                        iconPath: lgTransp
                    }
                },
                {
                    onSuccess: function (results) {
                        AfficherInfosRelais(results);
                    },
                    onError: function (error) {
                        alert('Whoops' + error);
                    }
                });
    }


    if (first === false) {
        myMap.panTo(point);
    } else {
        first = false; // we-plus modif
    }
}

function resolveAmbiguity() {
    console.log('resolveAmbi');
    var param = $("#selListeAdresses option:selected").text();
    indexfin = param.length;
    indexdeb = param.indexOf("(");
    indexsep = param.indexOf(",");

    latL = parseFloat(param.substr(indexdeb + 1, indexsep - indexdeb - 1));
    lonL = parseFloat(param.substr(indexsep + 1, indexfin - 1));
    searchPOIsByPoint({ lat: latL, lon: lonL });
    // myMap.panTo({ lat: latL, lon: lonL });
}

function DateCorrecte(date) {
    if (date == "Z")
        return "NaN";
    else
        return date;
}
function DateCorrecteII(date) {
    if (date == "NaN")
        return "";
    else
        return date;
}

function AfficherInfosRelais(results) {
    POIs = results;
    var icone = "";
    poiHTMLLst = "";
    var markers = [];
    var datas = new Array();
    var datasMeta = new Array();
    var relaisMax = 0;

    for (var i = 0; i < results.nbFound; ++i) {

        var monPOI = results.poiList[i].poi;
        relaisMax = 0;
        var k = 0;
        var temp = 1;
        for (var j = 0; j < monPOI.datasheet.descList.length; ++j) {

            var poiDatasheetDescList = monPOI.datasheet.descList[j];

            if (poiDatasheetDescList.idx == 1 ||
                poiDatasheetDescList.idx == 2 || poiDatasheetDescList.idx == 3
                || poiDatasheetDescList.idx == 4 || poiDatasheetDescList.idx == 5 ||
                poiDatasheetDescList.idx == 6 || poiDatasheetDescList.idx == 7
                || poiDatasheetDescList.idx == 8 || poiDatasheetDescList.idx == 9
                || poiDatasheetDescList.idx == 10 || poiDatasheetDescList.idx == 11
                || poiDatasheetDescList.idx == 12 || poiDatasheetDescList.idx == 13
                || poiDatasheetDescList.idx == 14 || poiDatasheetDescList.idx == 15
                || poiDatasheetDescList.idx == 16 || poiDatasheetDescList.idx == 17
                || poiDatasheetDescList.idx == 18 || poiDatasheetDescList.idx == 19
                || poiDatasheetDescList.idx == 20 || poiDatasheetDescList.idx == 21
                || poiDatasheetDescList.idx == 22 || poiDatasheetDescList.idx == 23
                || poiDatasheetDescList.idx == 24 || poiDatasheetDescList.idx == 25
                || poiDatasheetDescList.idx == 26 || poiDatasheetDescList.idx == 27
                || poiDatasheetDescList.idx == 28 || poiDatasheetDescList.idx == 29
                || poiDatasheetDescList.idx == 30) {
                if (temp == poiDatasheetDescList.idx) {
                    datas[k] = poiDatasheetDescList.value;
                    k++;
                    temp++;

                    if (poiDatasheetDescList.idx == 4) {
                        k++;
                        temp++;
                    }
                }
                else {
                    var limit = poiDatasheetDescList.idx;

                    for (var l = k; l < poiDatasheetDescList.idx; l++) {
                        datas[l] = "";
                    }

                    temp = poiDatasheetDescList.idx;
                    k = poiDatasheetDescList.idx - 1;

                    datas[k] = poiDatasheetDescList.value;
                    k++;
                    temp++;

                    if (poiDatasheetDescList.idx == 4) {
                        k++;
                        temp++;
                    }
                }
            }
        }

        var tmp = 1;
        var v = 0;
        for (var u = 0; u < monPOI.datasheet.metanumList.length; ++u) {
            var poiDatasheetMetanumList = monPOI.datasheet.metanumList[u];
            if (poiDatasheetMetanumList.idx == 1 || poiDatasheetMetanumList.idx == 2
                || poiDatasheetMetanumList.idx == 10) {
                if (tmp == poiDatasheetMetanumList.idx) {
                    datasMeta[v] = poiDatasheetMetanumList.value;
                    v++;
                    tmp++;
                }
            }
        }

        if (datasMeta[1] == "1")
            relaisMax = 1;

        DateDernierColis = DateCorrecte(datas[1]);
        DateFermeture = DateCorrecte(datas[2]);
        DatePremierColis = DateCorrecte(datas[3]);
        DateCreation = DateCorrecte(datas[19]);
        LienChoix = AffichageLienRelais(monPOI.id, ValeurDate2(DateDernierColis), ValeurDate2(DatePremierColis), ValeurDate2(DateCreation));

        poiHTML = "<div><img id='relais_img' style ='border-color:Black; border-width: 1; width : 150px' src='" + datas[28] + "' /></div><div style='width:195px;font-size:10px;'><div style='color:DarkBlue; width:300px'><strong>"
            + monPOI.name.toUpperCase() + " (" + results.poiList[i].dist + "m)"
            + "</strong></div><table style='width:300px' cellspacing='0' cellpadding='0'><tr><td style='width:130px'>";
        poiHTML += monPOI.location.formattedAddressLine;
        poiHTML += "</td></tr>";
        poiHTML += "<tr><td>" + monPOI.location.postalCode + " " + monPOI.location.city + "</td></tr>";

        poiHTML += "</table>";
        var urlSuite = "&reladr=" + escape(monPOI.location.formattedAddressLine) + "&relcp=" + monPOI.location.postalCode + "&relvil=" + escape(monPOI.location.city)
            + "&OuvLun=" + datas[5] + "@" + datas[6]
            + "&OuvMar=" + datas[7] + "@" + datas[8]
            + "&OuvMer=" + datas[9] + "@" + datas[10]
            + "&OuvJeu=" + datas[11] + "@" + datas[12]
            + "&OuvVen=" + datas[13] + "@" + datas[14]
            + "&OuvSam=" + datas[15] + "@" + datas[16]
            + "&OuvDim=" + datas[17] + "@" + datas[18]
            + "&PseudoRvc=" + datas[0]
            + "&fadr=" + escape(document.getElementById("form_address").value)
            + "&fcp=" + document.getElementById("form_CP").value
            + "&fvil=" + escape(document.getElementById("form_city").value)
            + "&frcmax=" + rcmax
            + "&flocrel=" + locRel
            + "&fcodpays=" + mycountryIsoCode
            + "&TypeLiv=REL"
            + "&age_code=" + datas[20] + "&age_nom="
            + datas[21] + "&age_adr=" + datas[22] + " "
            + datas[23] + "&age_vil=" + datas[25]
            + "&age_cp=" + datas[24];
        if (LienChoix == "OK") {
            poiHTML += "<div style='margin:10px 0px;'><a class='relais-link' target='_parent' href='" + redirect_link_rc + "?rel=" + monPOI.id + "&nom=" + escape(monPOI.name.toUpperCase())
                + urlSuite + "'>Je choisis ce Relais Colis®</a></div>";
        } else {
            poiHTML += "<div style='font-size:10px; color:red; width:350px; font-weight:bold'>"
                + "Relais en congés du " + DateCorrecteII(DateFermeture) + " au " + DateCorrecteII(DatePremierColis) + "</div>";
        }
        poiHTML += "<table style='width:170px;border:navy 1px solid;' cellspacing='0' cellpadding='0'><col style='width:50px'/><col style='width:60px'/><col style='width:60px'/>";
        poiHTML += "<tr><td colspan='3' align='center'><b>Horaires d'ouvertures</b></td></tr>";
        poiHTML += "<tr><td>Lundi</td><td align='center'>" + datas[5] + "</td><td align='center'>" + datas[6] + "</td></tr>";
        poiHTML += "<tr><td>Mardi</td><td align='center'>" + datas[7] + "</td><td align='center'>" + datas[8] + "</td></tr>";
        poiHTML += "<tr><td>Mercredi</td><td align='center'>" + datas[9] + "</td><td align='center'>" + datas[10] + "</td></tr>";
        poiHTML += "<tr><td>Jeudi</td><td align='center'>" + datas[11] + "</td><td align='center'>" + datas[12] + "</td></tr>";
        poiHTML += "<tr><td>Vendredi</td><td align='center'>" + datas[13] + "</td><td align='center'>" + datas[14] + "</td></tr>";
        poiHTML += "<tr><td>Samedi</td><td align='center'>" + datas[15] + "</td><td align='center'>" + datas[16] + "</td></tr>";
        poiHTML += "<tr><td>Dimanche</td><td align='center'>" + datas[17] + "</td><td align='center'>" + datas[18] + "</td></tr>";
        poiHTML += "</table>";

        poiHTML += "</div>";
        if (LienChoix == "OK") {
            if (relaisMax == 1)
                icone = lgRelaisColisMaxOuvert;
            else
                icone = lgRelaisColisOuvert;

            if (monPOI.datasheet.iconId == "3")
                icone = lgRelaisColisNew;
        }
        else {
            if (relaisMax == 1)
                icone = lgRelaisColisMaxFerme;
            else
                icone = lgRelaisColisFerme;

            if (monPOI.datasheet.iconId == "3")
                icone = lgRelaisColisNew;
        }
        if (monPOI.layer !== null) {
            var marker = new ViaMichelin.Api.Map.Marker({
                coords: { lon: monPOI.layer.coords.lon, lat: monPOI.layer.coords.lat },
                title: monPOI.name.toUpperCase(),
                htm: poiHTML,
                icon: { url: icone, offset: { x: offsetX, y: offsetY } }
            });
            myMap.addLayer(marker);
        }

        poiHTMLLst += "<div class='wrapper-locate'><div class='locate-info' ><div class='locate-title' >"
            + "<span class='num-locate'>" + (i + 1) + "</span><a onclick='javascript:poi_Locate(\"" + monPOI.location.coords.lat + "\",\"" + monPOI.location.coords.lon + "\");'>"
            + monPOI.name.toUpperCase() + " (" + results.poiList[i].dist + "m)"
            + "</a></div>";

        poiHTMLLst += monPOI.location.formattedAddressLine;
        poiHTMLLst += "<br/>" + monPOI.location.postalCode + " " + monPOI.location.city;

        poiHTMLLst += "<br/><b><a href = 'javascript:;' style = 'color: black' onclick = 'toggle(\"horaires_" + i + "\")'>Horaires d'ouverture</a></b><br/></div>";

        poiHTMLLst += "<table id = 'horaires_" + i + "' cellpadding='0' cellspacing='0'  style = 'display: block;>"
            + "<col style='width:55px' /><col style='width:62px' /><col style='width:85px' />"
            + "<tr align = 'center'><td align = 'left'>Lundi</td><td align = 'left'>" + datas[5] + "</td><td>" + datas[6] + "</td></tr>"
            + "<tr align = 'center'><td align = 'left'>Mardi</td><td align = 'left'>" + datas[7] + "</td><td>" + datas[8] + "</td></tr>"
            + "<tr align = 'center'><td align = 'left'>Mercredi</td><td align = 'left'>" + datas[9] + "</td><td>" + datas[10] + "</td></tr>"
            + "<tr align = 'center'><td align = 'left'>Jeudi</td><td align = 'left'>" + datas[11] + "</td><td>" + datas[12] + "</td></tr>"
            + "<tr align = 'center'><td align = 'left'>Vendredi</td><td align = 'left'>" + datas[13] + "</td><td>" + datas[14] + "</td></tr>"
            + "<tr align = 'center'><td align = 'left'>Samedi</td><td align = 'left'>" + datas[15] + "</td><td>" + datas[16] + "</td></tr>"
            + "<tr align = 'center'><td align = 'left'>Dimanche</td><td align = 'left'>" + datas[17] + "</td><td>" + datas[18] + "</td></tr>"
            + "</table>";

        if (LienChoix == "OK") {
            poiHTMLLst += "<div style='font-size:11px;margin:10px 0px'><a class='relais-link' target='_parent' href='" + redirect_link_rc + "?rel=" + monPOI.id + "&nom=" + escape(monPOI.name.toUpperCase())
                + urlSuite
                + "'>Je choisis ce Relais Colis®</a></div>";
        } else {
            poiHTMLLst += "<div style='font-size:10px; width:350px; font-weight:bold; color:red'>"
                + "Relais en congés du " + DateCorrecteII(DateFermeture) + " au " + DateCorrecteII(DatePremierColis) + "</div>";
        }
        poiHTMLLst += "<br/>";
        poiHTMLLst += "</div>";
    }
    document.getElementById("divInfohtml").innerHTML = poiHTMLLst;
}

function poi_Locate(latL, lonL) {
    myMap.panTo({ lat: latL, lon: lonL });
}

function getURLParam(strParamName) {
    var strReturn = "";
    var strHref = window.location.href;
    if (strHref.indexOf("?") > -1) {
        var strQueryString = strHref.substr(strHref.indexOf("?")).toLowerCase();
        var aQueryString = strQueryString.split("&");
        for (var iParam = 0; iParam < aQueryString.length; iParam++) {
            if (aQueryString[iParam].indexOf(strParamName.toLowerCase() + "=") > -1) {
                var aParam = aQueryString[iParam].split("=");
                strReturn = aParam[1] + " ";
                break;
            }
        }
    }
    return decodeURI(strReturn);
}

function getURLParam2(strParamName) {
    var strReturn = "";
    var strHref = window.location.href;
    if (strHref.indexOf("?") > -1) {
        var strQueryString = strHref.substr(strHref.indexOf("?")).toLowerCase();
        var aQueryString = strQueryString.split("&");
        for (var iParam = 0; iParam < aQueryString.length; iParam++) {
            if (aQueryString[iParam].indexOf(strParamName + "=") > -1) {
                var aParam = aQueryString[iParam].split("=");
                strReturn = aParam[1];
                break;
            }
        }
    }
    return strReturn;
}

function Init() {
    if (getURLParam("fadr") != "" || getURLParam("fcp") != "" || getURLParam("fvil") != "") {
        document.getElementById("form_address").value = getURLParam("fadr").toUpperCase();
        document.getElementById("form_CP").value = getURLParam("fcp").toUpperCase();
        document.getElementById("form_city").value = getURLParam("fvil").toUpperCase();
        rcmax = getURLParam2("frcmax").toUpperCase();
        locRel = getURLParam2("flocrel").toUpperCase();
        if (locRel != "") {
            if (locRel != "BEL" && locRel != "FRA" && locRel != "MCO")
                return "KO";
        } else {
            locRel = "FRA";
        }
        return "OK";
    } else {
        return "KO";
    }
}

function AffichageLienRelais(RelXst, DateDernierColis, DatePremierColis, DateCreation) {
    var Delai = delaiLivJour * 24;
    var AfficherLien = "KO";
    var today = new Date();
    jour = today.getDate();
    mois = today.getMonth() + 1;
    annee = today.getFullYear();
    var JourJ = ValeurDate2(jour + "/" + mois + "/" + annee);

    if (DateDernierColis != "NaN" && DatePremierColis != "NaN") {
        if (JourJ + Delai >= DatePremierColis) {
            AfficherLien = "OK";
        }
        if (JourJ + Delai <= DateDernierColis) {
            AfficherLien = "OK";
        }
        if (JourJ + Delai > DateDernierColis && JourJ + Delai < DatePremierColis) {
            AfficherLien = "CO";
        }
    }
    else {
        if (DatePremierColis == "NaN") {
            AfficherLien = "NA";
            if (DateDernierColis != "NaN" && JourJ + Delai <= DateDernierColis) {
                AfficherLien = "OK";
            }
            else {
                AfficherLien = "NA";
            }
        }
        if (DateDernierColis == "NaN") {
            if (DatePremierColis != "NaN" && JourJ + Delai < DatePremierColis) {
                AfficherLien = "CO";
            }
            if (DatePremierColis != "NaN" && JourJ + Delai >= DatePremierColis) {
                AfficherLien = "OK";
            }
        }
    }
    return AfficherLien;
}

function ValeurDate2(Ladate) {
    var elem = Ladate.split("/");
    if (Ladate != "")
        return 24 * parseInt(elem[0], 10) + 30 * 24 * parseInt(elem[1], 10) + 365 * 24 * parseInt(elem[2], 10);
    else
        return "NaN";
}
