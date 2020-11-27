<template>
    <div class="nettix_hakukone">
        <div class="container-fluid">
            <div class="row">
                <div class="wb-md-3" v-if="this.$parent.rajapinnat >= 2">
                    <p v-if="this.$parent.lang == ''">Ajoneuvotyyppi</p>
                    <p v-else-if="this.$parent.lang == 'en'">Type</p>
                    <select id="ajoneuvotyyppi" @change="$parent.haeAjoneuvotjaTallennaAsetukset()" class="nettix_select">
                        <option v-for="ajoneuvo in this.$parent.asetukset.rajapinnat" v-bind:value="ajoneuvo">
                            {{ ajoneuvo }}
                        </option>
                    </select>
                </div>
                <div class="wb-md-3">
                    <p v-if="this.$parent.lang == ''">Ajoneuvolaji</p>
                    <p v-else-if="this.$parent.lang == 'en'">Vehicle type</p>
                      <select id="ajoneuvolaji" @change="haeMallit(true)" class="nettix_select">
                          <option v-if="this.$parent.lang == ''" value="">Kaikki</option>
                          <option v-else-if="this.$parent.lang == 'en'" value="">All</option>

                          <option v-for="ajoneuvolaji in this.$parent.ajoneuvolajit" v-bind:value="ajoneuvolaji.id">
                              {{ ajoneuvolaji.name }}
                          </option>
                      </select>
                </div>
                <div class="wb-md-3">
                    <p v-if="this.$parent.lang == ''">Merkki</p>
                    <p v-else-if="this.$parent.lang == 'en'">Make</p>
                    <select id="merkki" @change="haeMallit()" v-model="key" class="nettix_select">
                        <option v-if="this.$parent.lang == ''" value="">Kaikki</option>
                        <option v-else-if="this.$parent.lang == 'en'" value="">All</option>

                        <option v-for="merkki in this.$parent.merkit" v-bind:value="merkki.id">
                            {{ merkki.name }}
                        </option>
                    </select>
                </div>
                <div class="wb-md-3">
                    <p v-if="this.$parent.lang == ''">Malli</p>
                    <p v-else-if="this.$parent.lang == 'en'">Model</p>
                    <select id="malli" @change="pikahakuMalleille()" class="nettix_select">
                        <option v-if="this.$parent.lang == ''" value="">Kaikki</option>
                        <option v-else-if="this.$parent.lang == 'en'" value="">All</option>
                        
                        <option v-for="malli in this.$parent.mallit" v-bind:value="malli.id">
                            {{ malli.name }}
                        </option>
                    </select>
                </div>
                <div class="wb-md-3" v-if="this.$parent.rajapinnat < 2">
                    <p v-if="this.$parent.lang == ''">Vapaa haku</p>
                    <p v-else-if="this.$parent.lang == 'en'">Free search</p>
                    <input type="text" id="vapaa_haku" class="nettix_input">
                </div>
            </div>
            <div class="row">
                <div class="wb-md-3" v-if="this.$parent.asetukset.kilometrit == 'kylla'">
                    <p v-if="this.$parent.lang == ''">Kilometrit alkaen</p>
                    <p v-else-if="this.$parent.lang == 'en'">Kilometers from</p>
                    <select id="kilometrit_alkaen" class="nettix_select">
                        <option value=""></option>
                        <option v-for="kilometri in this.kilometritArr" v-bind:value="kilometri">
                            {{ kilometri }} km
                        </option>
                    </select>
                </div>
                <div class="wb-md-3" v-if="this.$parent.asetukset.kilometrit == 'kylla'">
                    <p v-if="this.$parent.lang == ''">Kilometrit päättyen</p>
                    <p v-else-if="this.$parent.lang == 'en'">Kilometers to</p>
                    <select id="kilometrit_paattyen" class="nettix_select">
                        <option value=""></option>
                        <option v-for="kilometri in this.kilometritArr" v-bind:value="kilometri">
                            {{ kilometri }} km
                        </option>
                    </select>
                </div>
                <div class="wb-md-3" v-if="this.$parent.asetukset.hinta == 'kylla'">
                    <p v-if="this.$parent.lang == ''">Hinta alkaen</p>
                    <p v-else-if="this.$parent.lang == 'en'">Price from</p>
                    <input type="text" id="hinta_alkaen" class="nettix_input">
                </div>
                <div class="wb-md-3" v-if="this.$parent.asetukset.hinta == 'kylla'">
                    <p v-if="this.$parent.lang == ''">Hinta päättyen</p>
                    <p v-else-if="this.$parent.lang == 'en'">Price to</p>
                    <input type="text" id="hinta_paattyen" class="nettix_input">
                </div>

                <div class="wb-md-3" v-if="this.$parent.asetukset.tilavuus == 'kylla'">
                    <p v-if="this.$parent.lang == ''">Tilavuus alkaen</p>
                    <p v-else-if="this.$parent.lang == 'en'">Volume from</p>
                    <select id="tilavuus_alkaen" class="nettix_select">
                        <option value=""></option>
                        <option v-for="tilavuus in this.tilavuusArr" v-bind:value="tilavuus">
                            {{ tilavuus }} cm3
                        </option>
                    </select>
                </div>
                <div class="wb-md-3" v-if="this.$parent.asetukset.tilavuus == 'kylla'">
                    <p v-if="this.$parent.lang == ''">Tilavuus päättyen</p>
                    <p v-else-if="this.$parent.lang == 'en'">Volume to</p>
                    <select id="tilavuus_paattyen" class="nettix_select">
                        <option value=""></option>
                        <option v-for="tilavuus in this.tilavuusArr" v-bind:value="tilavuus">
                            {{ tilavuus }} cm3
                        </option>
                    </select>
                </div>
                <div class="wb-md-3" v-if="this.$parent.asetukset.vuosimalli == 'kylla'">
                    <p v-if="this.$parent.lang == ''">Vuosimalli alkaen</p>
                    <p v-else-if="this.$parent.lang == 'en'">Year from</p>
                    <select id="vuosimalli_alkaen" class="nettix_select">
                        <option value=""></option>
                        <option v-for="vuosi in this.yearArr" v-bind:value="vuosi">
                            {{ vuosi }}
                        </option>
                    </select>
                </div>
                <div class="wb-md-3" v-if="this.$parent.asetukset.vuosimalli == 'kylla'">
                    <p v-if="this.$parent.lang == ''">Vuosimalli päättyen</p>
                    <p v-else-if="this.$parent.lang == 'en'">Year to</p>
                    <select id="vuosimalli_paattyen" class="nettix_select">
                        <option value=""></option>
                        <option v-for="vuosi in this.yearArr" v-bind:value="vuosi">
                            {{ vuosi }}
                        </option>
                    </select>
                </div>
                <div class="wb-md-3" v-if="this.$parent.asetukset.paikkakunta == 'kylla'">
                    <p v-if="this.$parent.lang == ''">Paikkakunta</p>
                    <p v-else-if="this.$parent.lang == 'en'">Town</p>

                    <select v-if="this.$parent.lang == ''" id="kaupunki" class="nettix_select">
                        <option value="">Kaikki</option>
                        <option v-for="kaupunki in this.$parent.paikkakunnat" v-bind:value="kaupunki.id">
                            {{ kaupunki.name }}
                        </option>
                    </select>
                    <select v-else-if="this.$parent.lang == 'en'" id="kaupunki" class="nettix_select">
                        <option value="">All</option>
                        <option v-for="kaupunki in this.$parent.paikkakunnat" v-bind:value="kaupunki.id">
                            {{ kaupunki.name }}
                        </option>
                    </select>
                </div>
                <div class="wb-md-3" v-if="this.$parent.asetukset.maakunta == 'kylla'">
                    <p v-if="this.$parent.lang == ''">Maakunta</p>
                    <p v-else-if="this.$parent.lang == 'en'">Region</p>

                    <select v-if="this.$parent.lang == ''" id="maakunta" class="nettix_select">
                        <option value="">Kaikki</option>
                        <option v-for="maakunta in this.$parent.maakunnat" v-bind:value="maakunta.id">
                            {{ maakunta.name }}
                        </option>
                    </select>
                    <select v-else-if="this.$parent.lang == 'en'" id="maakunta" class="nettix_select">
                        <option value="">All</option>
                        <option v-for="maakunta in this.$parent.maakunnat" v-bind:value="maakunta.id">
                            {{ maakunta.name }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="wb-md-6">
                    <div v-if="this.$parent.lang == ''" id="nettix_haku_btn" v-on:click="$parent.haeAjoneuvotHaku('multiple', 1, false)">Haku</div>
                    <div v-else-if="this.$parent.lang == 'en'" id="nettix_haku_btn" v-on:click="$parent.haeAjoneuvotHaku('multiple', 1, false)">Search</div>
                </div>
                <div class="wb-md-6">
                    <div v-if="this.$parent.lang == ''" id="nettix_tyhjenna_btn" v-on:click="$parent.tyhjennaKentat()">Tyhjennä</div>
                    <div v-else-if="this.$parent.lang == 'en'" id="nettix_tyhjenna_btn" v-on:click="$parent.tyhjennaKentat()">Empty fields</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios'
    import Qs from 'qs'

    export default {
        created: function () {
            this.yearsGenerator()
        },
        data: function(){
            return {
                key: '',
                yearArr: [],
                tilavuusArr: [50,60,65,70,80,90,100,110,125,150,175,200,250,300,350,400,450,500,550,600,650,600,750,800,850,900,950,1000,1100,1200,1300,1500,1700,1900,2000,2100,2500,3000,3500],
                kilometritArr: [1000,2000,3000,4000,5000,10000,15000,20000,30000,50000,80000,100000,150000,200000,300000,500000],
                kilometritArrDesc: []
            }
        },
        methods: {
            haeMallit(ajoneuvolajihaku = false) {
                var obj = {}
                if(document.getElementById('ajoneuvotyyppi') != null) {
                    var ajoneuvotyyppi = document.getElementById('ajoneuvotyyppi').value
                } else {
                    var ajoneuvotyyppi = this.$parent.asetukset.rajapinnat[0]
                }

                if(document.getElementById('ajoneuvolaji') != null) {
                    var ajoneuvolaji = document.getElementById('ajoneuvolaji').value
                } else {
                    var ajoneuvolaji = ''
                }
                
                var id = this.key

                obj['ajoneuvotyyppi'] = ajoneuvotyyppi
                obj['ajoneuvolaji'] = ajoneuvolaji
                obj['id'] = id

                var sendDataAjax = JSON.stringify(obj);

                var data = {
                    'action': 'getModels',
                    'sendData': sendDataAjax
                }

                var vm = this;

                var url = wb_nettixAdminAjax.ajaxurl

                this.$parent.loader = true
                this.$parent.mallit = []

                if(ajoneuvolajihaku == true) {
                  this.$parent.merkit = []
                }

                axios.post(url, Qs.stringify(data))
                    .then(function (response) {
                        var arr = response.data

                        vm.$parent.mallit = []

                        for(var i=0; i < arr.length; i++) {
                            if(vm.$parent.lang == 'en') {
                                vm.$parent.mallit.push({
                                    name: arr[i].name_en,
                                    id:  arr[i].id
                                });
                            } else {
                                vm.$parent.mallit.push({
                                    name: arr[i].name,
                                    id:  arr[i].id
                                });
                            }
                        }

                        vm.$parent.loader = false

                        vm.$parent.haeAjoneuvot('multiple')
                    }).catch(function (error) {
                        console.log(error)
                });
            },
            pikahakuMalleille() {
                this.$parent.loader = false

                if(this.$parent.asetukset.pikahaku == 'kylla') {
                    this.$parent.haeAjoneuvot('multiple')
                }
            },
            yearsGenerator() {
                var year = new Date().getFullYear() + 2

                for(var a=1900; a<year; a++) {
                    this.yearArr.push(a)
                }

                this.yearArr.sort(function(a, b){
                    return b-a
                })
            }
        }
    }

</script>
