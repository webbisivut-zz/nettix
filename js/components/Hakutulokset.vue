<template>
    <div class="nettix_hakutulokset_wrap">
        <div class="container-fluid" v-if="this.$parent.eirajapintojaerror">
            <div class="row">
                <div class="wb-md-12"> 
                    <h2 v-if="$parent.lang == ''">Virhe! Rajapintoja ei valittu asetuksista!</h2>
                    <h2 v-else-if="$parent.lang == 'en'">Error! No APIs selected from the settings.</h2>
                </div>
            </div>
        </div>
        <div class="container-fluid" v-if="$parent.eiajoneuvojaerror">
            <div class="row">
                <div class="wb-md-12"> 
                    <h2 v-if="$parent.lang == ''">Ei hakua vastaavia ajoneuvoja saatavilla. Ole hyvä ja muuta hakua.</h2>
                    <h2 v-else-if="$parent.lang == 'en'">No search results. Try to change your search terms.</h2>
                </div>
            </div>
        </div>
       <div class="container-fluid">
            <div class="row" v-for="i in Math.ceil($parent.ajoneuvot.length / 3)" :key="i">
                <div class="wb-md-4" v-for="vehicle in $parent.ajoneuvot.slice((i - 1) * 3, i * 3)" :key="vehicle.make">
                    <div v-if="$parent.asetukset.teema == 'teema1'" class="nettix_vehicle">
                        <div @click="additionalInfo(vehicle.id)" v-scroll-to="'#nettix_hakutulokset_tag'" class="nettix_img" :style="imagestyles(typeof vehicle.images[0] === 'undefined' || vehicle.images[0] === null ? $parent.defaultImg : vehicle.images[0].medium['url'])">
                        </div>
                        <div class="nettix_details_bar_big2">
                            {{ vehicle.make === null || typeof vehicle.make === 'undefined' ? '' : vehicle.make['name'] }} {{ vehicle.model === null || typeof vehicle.model === 'undefined' ? '' : vehicle.model['name'] }} 
                        </div>
                        
                        <ul class="nettix_details_bar_big_ul2">
                            <li>{{ vehicle.year }}</li>
                            <li>{{ vehicle.kilometers }}km</li>
                            <li>{{ vehicle.price }}€</li>
                        </ul>

                        <div v-if="$parent.lang == '' && $parent.cpt == 'false'" class="nettix_lisatiedot_btn" :id="vehicle.id" v-scroll-to="'#nettix_hakutulokset_tag'" @click="additionalInfo(vehicle.id)">Lisätiedot</div>
                        <div v-else-if="$parent.lang == 'en' && $parent.cpt == 'false'" class="nettix_lisatiedot_btn" :id="vehicle.id" v-scroll-to="'#nettix_hakutulokset_tag'" @click="additionalInfo(vehicle.id)">Additional info</div>
                        <div v-else-if="$parent.lang == '' && $parent.cpt == 'true'" class="nettix_lisatiedot_btn" :id="vehicle.id" @click="additionalInfo(vehicle.id)">Lisätiedot</div>
                        <div v-else-if="$parent.lang == 'en' && $parent.cpt == 'true'" class="nettix_lisatiedot_btn" :id="vehicle.id" @click="additionalInfo(vehicle.id)">Additional info</div>
                    </div>
                    <div v-else class="nettix_vehicle">
                        <div @click="additionalInfo(vehicle.id)" v-scroll-to="'#nettix_hakutulokset_tag'" class="nettix_img" :style="imagestyles(typeof vehicle.images[0] === 'undefined' || vehicle.images[0] === null ? $parent.defaultImg : vehicle.images[0].medium['url'])">
                        </div>
                        <b>{{ vehicle.make === null || typeof vehicle.make === 'undefined' ? '' : vehicle.make['name'] }} {{ vehicle.model === null || typeof vehicle.model === 'undefined' ? '' : vehicle.model['name'] }} {{ vehicle.modelType === null || typeof vehicle.modelType === 'undefined' ? '' : vehicle.modelType['name'] }} </b><br>
                        <span v-if="$parent.lang == ''" >Vuosimalli: {{ vehicle.year }}<br></span>
                        <span v-else-if="$parent.lang == 'en'" >Year: {{ vehicle.year }}<br></span>

                        <span v-if="$parent.lang == ''" >Hinta: {{ vehicle.price }}€<br></span>
                        <span v-else-if="$parent.lang == 'en'" >Price: {{ vehicle.price }}€<br></span>

                        <span v-if="$parent.lang == '' && vehicle.kilometers != ''" >Mittarilukema: {{ vehicle.kilometers }}km<br></span>
                        <span v-else-if="$parent.lang == 'en' && vehicle.kilometers != ''" >Mileage: {{ vehicle.kilometers }}km<br></span>

                        <span v-if="$parent.lang == '' && $parent.asetukset.paikkakunta == 'kylla'" >Sijainti: {{ vehicle.town.fi }}<br></span>
                        <span v-else-if="$parent.lang == 'en' && $parent.asetukset.paikkakunta == 'kylla'" >Location: {{ vehicle.town.en }}<br></span>

                        <div v-if="$parent.lang == '' && $parent.cpt == 'false'" class="nettix_lisatiedot_btn" :id="vehicle.id" v-scroll-to="'#nettix_hakutulokset_tag'" @click="additionalInfo(vehicle.id)">Lisätiedot</div>
                        <div v-else-if="$parent.lang == 'en' && $parent.cpt == 'false'" class="nettix_lisatiedot_btn" :id="vehicle.id" v-scroll-to="'#nettix_hakutulokset_tag'" @click="additionalInfo(vehicle.id)">Additional info</div>
                        <div v-else-if="$parent.lang == '' && $parent.cpt == 'true'" class="nettix_lisatiedot_btn" :id="vehicle.id" @click="additionalInfo(vehicle.id)">Lisätiedot</div>
                        <div v-else-if="$parent.lang == 'en' && $parent.cpt == 'true'" class="nettix_lisatiedot_btn" :id="vehicle.id" @click="additionalInfo(vehicle.id)">Additional info</div>
                    </div>
                </div>
            </div>
            <div class="row" :class="{ hide_pagination : this.vehiclesPaginated <= 1 }">
                <div class="wb-md-12">
                    <div class="nettix_slick_wrap">
                            <slick ref="slick" :options="slickOptions">
                                <div class="nettix_pagination" v-scroll-to="'#nettix_hakutulokset_tag'" v-for="i in this.vehiclesPaginated" :key="i" :class="{ nettix_selected : $parent.pagenumber == i }" @click="$parent.changePage(i)">
                                    {{ i }}
                                </div>
                            </slick>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Slick from 'vue-slick'
    import axios from 'axios'
    import Qs from 'qs'

    export default {
        components: { Slick },
        data() {
            return {
                slickOptions: {
                    slidesToShow: 10,
                    slidesToScroll: 10,
                    // Any other options that can be got from plugin documentation
                },
                vehiclesPaginated: 0
            };
        },
        beforeUpdate() {
            if (this.$refs.slick) {
                this.$refs.slick.destroy();
            }
        },
        updated() {
            this.$nextTick(function () {
                if (this.$refs.slick) {
                    this.vehiclesPaginated = this.$parent.vehiclesPaginated

                    if(this.vehiclesPaginated > 1) {
                        this.$refs.slick.create(this.slickOptions)
                    }
                    
                }
            });
        },
        methods: {
            imagestyles(url) {
                return { 'background-image': 'url(' + url + ')' }
            },
            additionalInfo(id) {
                if(this.$parent.cpt == 'true') {
                    this.$parent.haeSinglesivu(id)
                } else {
                    this.$parent.view = 'single'
                    this.$parent.singleid = id

                    if(this.$parent.asetukset.cpt !== 'kylla') {
                        window.history.pushState("", "", "?id=" + id + "&tyyppi=" + this.$parent.ajoneuvotyyppi)
                    }
                }
                
                if(this.$parent.asetukset.ga == 'kylla' && typeof ga != 'undefined') {
                    ga('send', 'event', 'click', 'Nettix vehicle details button clicked with id: ' + id)
                }
            },
            next() {
                this.$refs.slick.next();
            },
            prev() {
                this.$refs.slick.prev();
            },
            reInit() {
                this.$nextTick(() => {
                    this.$refs.slick.reSlick();
                });
            },
        }
    }

</script>
