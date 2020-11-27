<template>
    <div v-if="this.cpt === 'true' ? id='nettix_hakutulokset_tag_cpt' : id='nettix_hakutulokset_tag'">
        <div id="nettix_loader" class="nettix_loader" v-if="this.loader">
            <div class="nettix_spinner" v-if="this.loader"></div>
        </div>
        <hakukone v-if="view == 'multiple'"></hakukone>
        <hakutulokset v-if="view == 'multiple'"></hakutulokset>
        <single v-if="view == 'single'"></single>
    </div>
</template>

<script>
    import Hakukone from './Hakukone.vue';
    import Hakutulokset from './Hakutulokset.vue';
    import Single from './Single.vue';
    import axios from 'axios'
    import Qs from 'qs'

    export default {
        data: function(){
            return {
                asetukset: '',
                paikkakunnat: [],
                maakunnat: [],
                ajoneuvotyyppi: '',
                merkit: [],
                mallit: [],
                rajapinnat: 0,
                ajoneuvolajit: [],
                vehicleDetails: '',
                ajoneuvot: [],
                eirajapintojaerror: false,
                eiajoneuvojaerror: false,
                totalVehicles: 0,
                vehiclesPaginated: 0,
                pagenumber: 1,
                singleid: 0,
                loader: true,
                aloitus: 1,
                view: '',
                lang: '',
                cpt: 'false',
                siteurl: vue_url.site_url,
                defaultImg: vue_url.site_url + '/wp-content/plugins/wb-nettix/assets/img/no-photo-available.png',
            }
        },
        created: function () {
            this.getSettings()
        },
        mounted () {
            window.addEventListener(
                'popstate', this.readId
            ) 
        },
        destroyed () {
            window.removeEventListener(
                'popstate', this.readId
            ) 
        },
        methods: {
            getUrlID() {
                var urlParams;
                ((window).onpopstate = function () {
                var match,
                        pl     = /\+/g,  // Regex for replacing addition symbol with a space
                        search = /([^&=]+)=?([^&]*)/g,
                    decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
                    query  = window.location.search.substring(1);

                urlParams = {};
                    while (match = search.exec(query))
                    urlParams[decode(match[1])] = decode(match[2]);
                })();

                var getUrlID = urlParams["id"];

                return getUrlID;
            },
            getUrlType() {
                var urlParams;
                ((window).onpopstate = function () {
                var match,
                        pl     = /\+/g,  // Regex for replacing addition symbol with a space
                        search = /([^&=]+)=?([^&]*)/g,
                    decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
                    query  = window.location.search.substring(1);

                urlParams = {};
                    while (match = search.exec(query))
                    urlParams[decode(match[1])] = decode(match[2]);
                })();

                var getUrlType = urlParams["tyyppi"];

                return getUrlType;
            },
            readId() {
                // Luetaan mahdollinen id osoiteriviltä
                var id = this.getUrlID();

                if(document.getElementById('nettix_singleid') !== null) {
                    var shortcodeId = document.getElementById('nettix_singleid').innerHTML
                } else {
                    var shortcodeId = ''
                }
                
                // Jos löytyy ID, niin näytetään kohdetiedot. Muussa tapauksessa ladataan kaikki kohteet.
                if(shortcodeId != '') {
                    this.singleid = shortcodeId
                    this.view = 'single'
                    this.haeAjoneuvot('single')
                } else if (id && id != 'all') {
                    this.singleid = id
                    this.view = 'single'
                    this.haeAjoneuvot('single')
                } else {
                    this.singleid = 0
                    this.view = 'multiple'
                    this.haeAjoneuvot('multiple')
                }
            },
            getSettings() {
                // Tarkistetaan asetettu kieli
                if(document.getElementById('nettix_lang') != null) {
                    var kieli = document.getElementById('nettix_lang').innerHTML
                } else {
                    var kieli = ''
                }

                if(document.getElementById('nettix_cpt') != null) {
                    var cpt = document.getElementById('nettix_cpt').innerHTML
                } else {
                    var cpt = ''
                }

                if(kieli != '' && typeof kieli != null) {
                    this.lang = kieli
                }

                if(cpt != '' && typeof cpt != null) {
                    this.cpt = cpt
                }

                var data = {
                    'action': 'getSettings',
                    'sendData': ''
                };

                var vm = this

                var url = wb_nettixAdminAjax.ajaxurl

                this.loader = true

                axios.post(url, Qs.stringify(data))
                    .then(function (response) {
                        vm.asetukset = response.data
                        vm.eirajapintojaerror = false

                        var totalAPIcount = 0

                        for(var x=0; x < response.data.rajapinnat.length; x++) {
                            totalAPIcount++
                        }

                        vm.rajapinnat = totalAPIcount

                        if(typeof response.data.rajapinnat[0] != 'undefined') {
                            vm.ajoneuvotyyppi = response.data.rajapinnat[0]
                        } else {
                            vm.eirajapintojaerror = true
                        }

                        vm.readId()
                    }).catch(function (error) {
                        console.log(error)
                });
            },
            haeAjoneuvotjaTallennaAsetukset() {
                if(document.getElementById('ajoneuvotyyppi') != null) {
                    var ajoneuvotyyppi = document.getElementById('ajoneuvotyyppi').value
                } else {
                    var ajoneuvotyyppi = this.$parent.asetukset.rajapinnat[0]
                }

                this.ajoneuvolajit = []
                this.merkit = []
                this.mallit = []

                this.ajoneuvotyyppi = ajoneuvotyyppi
                this.aloitus = 1

                this.haeAjoneuvot('multiple', ajoneuvotyyppi)
            },
            haeAjoneuvot(view, ajoneuvotyyppi = null) {
                this.eirajapintojaerror = false

                if(ajoneuvotyyppi == null && document.getElementById('ajoneuvotyyppi') != null) {
                    var ajoneuvotyyppi = document.getElementById('ajoneuvotyyppi').value
                } 
                
                if(ajoneuvotyyppi == null) {
                    ajoneuvotyyppi = this.asetukset.rajapinnat[0]
                }

                if(typeof ajoneuvotyyppi == 'undefined' || ajoneuvotyyppi == '') {
                    this.eirajapintojaerror = true
                    this.loader = false
                } else {
                    var obj = {}

                    obj['ajoneuvotyyppi'] = ajoneuvotyyppi

                    var sendDataAjax = JSON.stringify(obj);

                    var data = {
                        'action': 'getVehicles',
                        'sendData': sendDataAjax
                    }

                    var view = view

                    var vm = this;

                    var url = wb_nettixAdminAjax.ajaxurl

                    this.loader = true

                    axios.post(url, Qs.stringify(data))
                        .then(function (response) {
                            var arr = JSON.parse(response.data)
                            var merkkiArr = []

                            vm.merkit = []

                            if(view == 'multiple' && document.getElementById('ajoneuvojali') != null) {
                                var ajoneuvolaji = document.getElementById('ajoneuvolaji').value
                            } else {
                                var ajoneuvolaji = ''
                            }

                            for(var a=0; a < arr.length; a++) {
                                if(vm.lang == 'en') {
                                    if(typeof vm.paikkakunnat[0] == 'undefined') {
                                        vm.paikkakunnat.push({
                                          name: arr[a].town.en,
                                          id:  arr[a].town.id
                                        })
                                    } else {
                                        if (vm.paikkakunnat.filter(function(e) { return e.name == arr[a].town.fi; }).length < 1) {
                                            vm.paikkakunnat.push({
                                                name: arr[a].town.en,
                                                id:  arr[a].town.id
                                            })
                                        }
                                    }

                                    if(typeof vm.maakunnat[0] == 'undefined') {
                                        vm.maakunnat.push({
                                          name: arr[a].region.en,
                                          id:  arr[a].region.id
                                        })
                                    } else {
                                        if (vm.maakunnat.filter(function(e) { return e.name == arr[a].region.fi; }).length < 1) {
                                            vm.maakunnat.push({
                                                name: arr[a].region.en,
                                                id:  arr[a].region.id
                                            })
                                        }
                                    }
                                } else {
                                    if(typeof vm.paikkakunnat[0] == 'undefined') {
                                        vm.paikkakunnat.push({
                                          name: arr[a].town.fi,
                                          id:  arr[a].town.id
                                        })
                                    } else {
                                        if (vm.paikkakunnat.filter(function(e) { return e.name == arr[a].town.fi; }).length < 1) {
                                            vm.paikkakunnat.push({
                                                name: arr[a].town.fi,
                                                id:  arr[a].town.id
                                            })
                                        }
                                    }

                                    if(typeof vm.maakunnat[0] == 'undefined') {
                                        vm.maakunnat.push({
                                          name: arr[a].region.fi,
                                          id:  arr[a].region.id
                                        })
                                    } else {
                                        if (vm.maakunnat.filter(function(e) { return e.name == arr[a].region.fi; }).length < 1) {
                                            vm.maakunnat.push({
                                                name: arr[a].region.fi,
                                                id:  arr[a].region.id
                                            })
                                        }
                                    }
                                }
                            }

                            if(ajoneuvotyyppi == 'Autot') {
                              for(var i=0; i < arr.length; i++) {
                                  if(!merkkiArr.includes(arr[i].make.name) && arr[i].vehicleType.id == ajoneuvolaji || !merkkiArr.includes(arr[i].make.name) && ajoneuvolaji == '') {
                                      vm.merkit.push({
                                          name: arr[i].make.name,
                                          id:  arr[i].make.id
                                      })

                                      merkkiArr.push(arr[i].make.name)
                                  }
                              }
                            } else if(ajoneuvotyyppi == 'Motot') {
                              for(var i=0; i < arr.length; i++) {
                                  if(!merkkiArr.includes(arr[i].make.name) && arr[i].bikeType.id == ajoneuvolaji || !merkkiArr.includes(arr[i].make.name) && ajoneuvolaji == '') {                                    
                                      vm.merkit.push({
                                          name: arr[i].make.name,
                                          id:  arr[i].make.id
                                      })

                                      merkkiArr.push(arr[i].make.name)
                                  }
                              }
                            }

                            if(vm.aloitus == 1) {
                              vm.haeAjoneuvolajit()
                            }

                            if(vm.asetukset.pikahaku == 'kylla' || vm.aloitus == 1) {
                                vm.haeAjoneuvotHaku(view)
                            } else if(vm.asetukset.pikahaku == 'ei' && vm.aloitus == 0) {
                                vm.loader = false
                            } else {
                                vm.haeAjoneuvotHaku(view)
                            }

                        }).catch(function (error) {
                            console.log(error)
                    });
                }

            },
            haeAjoneuvolajit() {
                var obj = {}
                if(document.getElementById('ajoneuvotyyppi') != null) {
                    var ajoneuvotyyppi = document.getElementById('ajoneuvotyyppi').value
                } else {
                    var ajoneuvotyyppi = this.asetukset.rajapinnat[0]
                }
                var id = this.key

                obj['ajoneuvotyyppi'] = ajoneuvotyyppi
                obj['id'] = id

                var sendDataAjax = JSON.stringify(obj);

                var data = {
                    'action': 'getAjoneuvolajit',
                    'sendData': sendDataAjax
                }

                var vm = this;

                var url = wb_nettixAdminAjax.ajaxurl

                this.ajoneuvolajit = []
                this.mallit = []

                axios.post(url, Qs.stringify(data))
                    .then(function (response) {
                        var arr = response.data

                        for(var i=0; i < arr.length; i++) {
                            if(vm.lang == 'en') {
                                vm.ajoneuvolajit.push({
                                    name: arr[i].name_en,
                                    id:  arr[i].id
                                });
                            } else {
                                vm.ajoneuvolajit.push({
                                    name: arr[i].name,
                                    id:  arr[i].id
                                });
                            }
                        }

                    }).catch(function (error) {
                        console.log(error)
                });
            },
            haeSinglesivu(id) {
                var obj = {}
                obj['id'] = id

                var sendDataAjax = JSON.stringify(obj);

                var data = {
                    'action': 'loadVehicleSingle',
                    'sendData': sendDataAjax
                }
                
                var vm = this;

                var url = wb_nettixAdminAjax.ajaxurl

                if(document.getElementById(id) != null) {
                    var load = document.getElementById(id)
                
                    if(this.lang == 'en') {
                        load.innerHTML = 'Please wait...'
                    } else {
                        load.innerHTML = 'Odota hetki...'
                    }
                }

                axios.post(url, Qs.stringify(data))
                    .then(function (response) {
                        window.location.href = response.data
                    }).catch(function (error) {
                    console.log(error)
                });
            },
            haeAjoneuvotHaku(view, pagenumber = 1, aloitus = true) {
                if(view == 'multiple' && this.asetukset.cpt !== 'kylla' && !this.aloitus) {
                    window.history.pushState("", "", "?id=all")
                }

                this.pagenumber = pagenumber

                if(aloitus == false) {
                    this.aloitus = 0
                }

                if(view == 'multiple') {
                    // Luetaan hakukoneen kentät ja haetaan tiedot
                    if(document.getElementById('ajoneuvotyyppi') != null) {
                        var ajoneuvotyyppi = document.getElementById('ajoneuvotyyppi').value
                    } else {
                        var ajoneuvotyyppi = this.asetukset.rajapinnat[0]
                    }

                    if(document.getElementById('ajoneuvolaji') != null) {
                        var ajoneuvolaji = document.getElementById('ajoneuvolaji').value
                    } else {
                        var ajoneuvolaji = ''
                    }

                    if(document.getElementById('merkki') != null) {
                        var merkki = document.getElementById('merkki').value
                    } else {
                        var merkki = ''
                    }

                    if(document.getElementById('malli') != null) {
                        var malli = document.getElementById('malli').value
                    } else {
                        var malli = ''
                    }

                    if(document.getElementById('vapaa_haku') != null) {
                        var vapaa_haku = document.getElementById('vapaa_haku').value
                    } else {
                        var vapaa_haku = ''
                    }

                    if(document.getElementById('kilometrit_alkaen') != null) {
                        var kilometrit_alkaen = document.getElementById('kilometrit_alkaen').value
                    } else {
                        var kilometrit_alkaen = ''
                    }

                    if(document.getElementById('kilometrit_paattyen') != null) {
                        var kilometrit_paattyen = document.getElementById('kilometrit_paattyen').value
                    } else {
                        var kilometrit_paattyen = ''
                    }

                    if(document.getElementById('hinta_alkaen') != null) {
                        var hinta_alkaen = document.getElementById('hinta_alkaen').value
                    } else {
                        var hinta_alkaen = ''
                    }

                    if(document.getElementById('hinta_paattyen') != null) {
                        var hinta_paattyen = document.getElementById('hinta_paattyen').value
                    } else {
                        var hinta_paattyen = ''
                    }

                    if(document.getElementById('tilavuus_alkaen') != null) {
                        var tilavuus_alkaen = document.getElementById('tilavuus_alkaen').value
                    } else {
                        var tilavuus_alkaen = ''
                    }

                    if(document.getElementById('tilavuus_paattyen') != null) {
                        var tilavuus_paattyen = document.getElementById('tilavuus_paattyen').value
                    } else {
                        var tilavuus_paattyen = ''
                    }

                    if(document.getElementById('vuosimalli_alkaen') != null) {
                        var vuosimalli_alkaen = document.getElementById('vuosimalli_alkaen').value
                    } else {
                        var vuosimalli_alkaen = ''
                    }

                    if(document.getElementById('vuosimalli_paattyen') != null) {
                        var vuosimalli_paattyen = document.getElementById('vuosimalli_paattyen').value
                    } else {
                        var vuosimalli_paattyen = ''
                    }

                    if(document.getElementById('kaupunki') != null) {
                        var kaupunki = document.getElementById('kaupunki').value
                    } else {
                        var kaupunki = ''
                    }

                    if(document.getElementById('maakunta') != null) {
                        var maakunta = document.getElementById('maakunta').value
                    } else {
                        var maakunta = ''
                    }
                } else {
                    // Luetaan hakukoneen kentät ja haetaan tiedot
                    var ajoneuvotyyppi = this.asetukset.rajapinnat[0]
                    var ajoneuvolaji = ''
                    var merkki = ''
                    var malli = ''
                    var vapaa_haku = ''
                    var kilometrit_alkaen = ''
                    var kilometrit_paattyen = ''
                    var hinta_alkaen = ''
                    var hinta_paattyen = ''
                    var tilavuus_alkaen = ''
                    var tilavuus_paattyen = ''
                    var vuosimalli_alkaen = ''
                    var vuosimalli_paattyen = ''
                    var kaupunki = ''
                    var maakunta = ''
                }
                
                var pagenumber = this.pagenumber

                var obj = {}

                obj['ajoneuvotyyppi'] = ajoneuvotyyppi
                obj['ajoneuvolaji'] = ajoneuvolaji
                obj['merkki'] = merkki
                obj['malli'] = malli
                obj['vapaa_haku'] = vapaa_haku

                obj['kilometrit_alkaen'] = kilometrit_alkaen
                obj['kilometrit_paattyen'] = kilometrit_paattyen
                obj['hinta_alkaen'] = hinta_alkaen
                obj['hinta_paattyen'] = hinta_paattyen

                obj['tilavuus_alkaen'] = tilavuus_alkaen
                obj['tilavuus_paattyen'] = tilavuus_paattyen
                obj['vuosimalli_alkaen'] = vuosimalli_alkaen
                obj['vuosimalli_paattyen'] = vuosimalli_paattyen
                
                obj['kaupunki'] = kaupunki
                obj['maakunta'] = maakunta

                obj['pagenumber'] = pagenumber
                obj['aloitus'] = this.aloitus

                var sendDataAjax = JSON.stringify(obj);

                var data = {
                    'action': 'getVehiclesSearch',
                    'sendData': sendDataAjax
                };

                var vm = this;

                var url = wb_nettixAdminAjax.ajaxurl

                this.loader = true

                this.ajoneuvot = []
                this.totalVehicles = 0
                this.vehiclesPaginated = 0

                this.view = view
                this.singleid = 0

                axios.post(url, Qs.stringify(data))
                    .then(function (response) {                         
                        vm.eiajoneuvojaerror = false

                        var arr1 = JSON.parse(response.data[0])
                        var arr2 = JSON.parse(response.data[1])

                        var totalVehicles = parseInt(arr2.total)
                        vm.totalVehicles = totalVehicles

                        var rows = vm.asetukset.rows

                        if(rows == '') {
                          rows = 30
                        }

                        vm.ajoneuvot = []
                        // Rajoitetaan hakutulosten määrää - tulikettu alkaa muuten käymään kuumana. Onko tähän parempaa ratkaisua?

                        if(totalVehicles > 10000) {
                            vm.vehiclesPaginated = 200
                        } else {
                          if(rows > totalVehicles) {
                            vm.vehiclesPaginated = 1

                            var kerroin = 0
                            vm.aloitus = 0

                            for(var a=kerroin; a < totalVehicles; a++) {
                                vm.ajoneuvot.push(arr1[a])
                            }
                          } else {
                            if(vm.aloitus == 1 && vm.pagenumber > 1) {
                                var kerroin = rows * (vm.pagenumber - 1)
                                var rowsTotal = parseInt(kerroin) + parseInt(rows)

                                if(totalVehicles < rowsTotal) {
                                    rowsTotal = totalVehicles
                                }
                            } else {
                                var kerroin = 0
                                var rowsTotal = rows
                            }

                            vm.vehiclesPaginated = Math.ceil(totalVehicles / rows)

                            for(var a=kerroin; a < rowsTotal; a++) {
                              if(typeof arr1[a] != 'undefined' && arr1[a] != null) {
                                  vm.ajoneuvot.push(arr1[a])
                              }
                            }

                          }
                        }

                        if(vm.ajoneuvot.length <= 0) {
                            vm.eiajoneuvojaerror = true
                        }

                        vm.loader = false

                    }).catch(function (error) {
                        console.log(error)
                });
            },
            tyhjennaKentat() {
                if(document.getElementById('vapaa_haku') != null) {
                    var vapaa_haku = document.getElementById('vapaa_haku').value = ''
                }

                if(document.getElementById('kilometrit_alkaen') != null) {
                    var kilometrit_alkaen = document.getElementById('kilometrit_alkaen').selectedIndex = 0
                }

                if(document.getElementById('kilometrit_paattyen') != null) {
                    var kilometrit_paattyen = document.getElementById('kilometrit_paattyen').selectedIndex = 0
                }

                if(document.getElementById('hinta_alkaen') != null) {
                    var hinta_alkaen = document.getElementById('hinta_alkaen').value = ''
                }

                if(document.getElementById('hinta_paattyen') != null) {
                    var hinta_paattyen = document.getElementById('hinta_paattyen').value = ''
                }

                if(document.getElementById('tilavuus_alkaen') != null) {
                    var tilavuus_alkaen = document.getElementById('tilavuus_alkaen').selectedIndex = 0
                }

                if(document.getElementById('tilavuus_paattyen') != null) {
                    var tilavuus_paattyen = document.getElementById('tilavuus_paattyen').selectedIndex = 0
                }

                if(document.getElementById('vuosimalli_alkaen') != null) {
                    var vuosimalli_alkaen = document.getElementById('vuosimalli_alkaen').selectedIndex = 0
                }

                if(document.getElementById('vuosimalli_paattyen') != null) {
                    var vuosimalli_paattyen = document.getElementById('vuosimalli_paattyen').selectedIndex = 0
                }
            },
            ajoneuvonTiedot() {
                // Ajax hakemaan kohteen tiedot
                var type = this.getUrlType()

                if(type) {
                    var ajoneuvotyyppi = type
                } else {
                    var ajoneuvotyyppi = this.ajoneuvotyyppi
                }

                var id = this.singleid

                var obj = {}

                obj['ajoneuvotyyppi'] = ajoneuvotyyppi
                obj['id'] = id

                var sendDataAjax = JSON.stringify(obj);

                var data = {
                    'action': 'getVehicleDetails',
                    'sendData': sendDataAjax
                }

                this.vehicleDetails = ''

                var vm = this

                var url = wb_nettixAdminAjax.ajaxurl

                this.loader = true

                axios.post(url, Qs.stringify(data))
                    .then(function (response) {
                        var arr = JSON.parse(response.data)
                        vm.vehicleDetails = arr

                        vm.loader = false
                    }).catch(function (error) {
                        console.log(error)
                });
            },
            changePage: function(pagenumber) {
                this.pagenumber = pagenumber

                this.haeAjoneuvotHaku('multiple', pagenumber)
            },
        },
        components: {
            'hakukone': Hakukone,
            'hakutulokset': Hakutulokset,
            'single': Single
        }
    }
</script>
