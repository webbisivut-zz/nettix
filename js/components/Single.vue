<template>
    <div class="nettix_hakutulokset_wrap" v-if="$parent.vehicleDetails != ''" id="nettix_hakutulokset_tag">
        <div class="container-fluid">
            <div class="row">
                <div :class="this.$parent.asetukset.mainos == 'kylla' || this.$parent.asetukset.lisatiedot == 'kylla' || this.$parent.asetukset.viesti == 'kylla' || this.$parent.asetukset.sijainti == 'kylla' ? 'wb-md-8' : 'wb-md-12'">
                    <div class="nettix_vehicle_title">
                        <h1> {{ $parent.vehicleDetails.make === null || typeof $parent.vehicleDetails.make === 'undefined' ? '' : $parent.vehicleDetails.make.name }} {{ $parent.vehicleDetails.model === null || typeof $parent.vehicleDetails.model === 'undefined' ? '' : $parent.vehicleDetails.model.name }} {{ $parent.vehicleDetails.modelType === null || typeof $parent.vehicleDetails.modelType === 'undefined' ? '' : $parent.vehicleDetails.modelType.name }} vm {{ $parent.vehicleDetails.year === null || typeof $parent.vehicleDetails.year === 'undefined' ? '' : $parent.vehicleDetails.year }}, {{ $parent.vehicleDetails.price === null || typeof $parent.vehicleDetails.price === 'undefined' ? '' : $parent.vehicleDetails.price }}€{{ $parent.asetukset.km_otsikossa == 'kylla' ? ', ' + $parent.vehicleDetails.kilometers + 'km' : ''}}</h1>
                        <h2> {{ $parent.vehicleDetails.modelTypeName === null || typeof $parent.vehicleDetails.modelTypeName === 'undefined' ? '' : $parent.vehicleDetails.modelTypeName }} </h2>
                    
                        <div v-if="this.$parent.lang == ''" @click="takaisin_hakutuloksiin()" class="nettix_takaisin_hakutuloksiin">&laquo; Takaisin hakutuloksiin</div>
                        <div v-else-if="this.$parent.lang == 'en'" @click="takaisin_hakutuloksiin()" class="nettix_takaisin_hakutuloksiin">&laquo; Back to search results</div>
                    </div>

                    <div class="nettix_vehicle_content">
                        <div id="nettix_vehicle_main_img_wrap">
                            <slick ref="slick" :options="slickOptions" class="slider-for" v-if="$parent.vehicleDetails.images != null && $parent.vehicleDetails.images != '' && typeof $parent.vehicleDetails.images != 'undefined' && $parent.vehicleDetails.images.length > 0">
                                <div class="main_img_wrap" v-for="i in $parent.vehicleDetails.images" :key="i.large.url">
                                    <a v-if="$parent.asetukset.kuvan_koko == 'large'" :href="i.large.url" data-lightbox="nettix_vehicles_images"><img class="large_item_img" :src="i.large.url"></a>
                                    <a v-else :href="i.large.url" data-lightbox="nettix_vehicles_images"><img class="medium_item_img" :src="i.medium.url"></a>
                                </div>
                            </slick>
                        </div>

                        <div id="nettix_vehicle_add_img_wrap">
                            <slick ref="slick2" :options="slickOptions2" class="slider-nav" v-if="$parent.vehicleDetails.images != null && $parent.vehicleDetails.images != '' && typeof $parent.vehicleDetails.images != 'undefined' && $parent.vehicleDetails.images.length > 1">
                                <div class="nettix_vehicles_thumb" v-for="i in $parent.vehicleDetails.images" :key="i.medium.url">
                                    <img style="margin: 0 auto;" :src="i.medium.url">
                                </div>
                            </slick>
                        </div>

                        <div class="nettix_vehicle_content_details">
                            <div class="nettix_img_single" :style="imagestyles(this.$parent.defaultImg)" v-if="typeof $parent.vehicleDetails.images === 'undefined' || $parent.vehicleDetails.images.length == 0 || $parent.vehicleDetails.images === null"></div>
                            <h2 v-if="this.$parent.lang == ''">Ajoneuvon tiedot</h2>
                            <h2 v-else-if="this.$parent.lang == 'en'">Vehicle details</h2>

                            <div class="nettix_detail nettix_description">{{ $parent.vehicleDetails.description === null || typeof $parent.vehicleDetails.description === 'undefined' ? '' : $parent.vehicleDetails.description }}</div>
                            
                            <div v-if="this.$parent.lang == ''" class="nettix_detail nettix_grey_bg merkki">Merkki: {{ $parent.vehicleDetails.make === null || typeof $parent.vehicleDetails.make === 'undefined' ? '' : $parent.vehicleDetails.make.name }}</div>
                            <div v-else-if="this.$parent.lang == 'en'" class="nettix_detail nettix_grey_bg merkki">Make: {{ $parent.vehicleDetails.make === null || typeof $parent.vehicleDetails.make === 'undefined' ? '' : $parent.vehicleDetails.make.name }}</div>
                            
                            <div v-if="this.$parent.lang == ''" class="nettix_detail malli">Malli: {{ $parent.vehicleDetails.model === null || typeof $parent.vehicleDetails.model === 'undefined' ? '' : $parent.vehicleDetails.model.name }}</div>
                            <div v-else-if="this.$parent.lang == 'en'" class="nettix_detail malli">Model: {{ $parent.vehicleDetails.model === null || typeof $parent.vehicleDetails.model === 'undefined' ? '' : $parent.vehicleDetails.model.name }}</div>
                            
                            <div v-if="this.$parent.lang == ''" class="nettix_detail nettix_grey_bg vuosimalli">Vuosimalli: {{ $parent.vehicleDetails.year === null || typeof $parent.vehicleDetails.year === 'undefined' ? '' : $parent.vehicleDetails.year }}</div>
                            <div v-else-if="this.$parent.lang == 'en'" class="nettix_detail nettix_grey_bg vuosimalli">Year: {{ $parent.vehicleDetails.year === null || typeof $parent.vehicleDetails.year === 'undefined' ? '' : $parent.vehicleDetails.year }}</div>
                            
                            <div v-if="this.$parent.lang == ''" class="nettix_detail hinta">Hinta: {{ $parent.vehicleDetails.price === null || typeof $parent.vehicleDetails.price === 'undefined' ? '' : $parent.vehicleDetails.price }}€</div>
                            <div v-else-if="this.$parent.lang == 'en'" class="nettix_detail hinta">Price: {{ $parent.vehicleDetails.price === null || typeof $parent.vehicleDetails.price === 'undefined' ? '' : $parent.vehicleDetails.price }}€</div>
                            
                            <div v-if="this.$parent.lang == ''" class="nettix_detail nettix_grey_bg tyyppi">Tyyppi: {{ $parent.vehicleDetails.bikeType === null || typeof $parent.vehicleDetails.bikeType === 'undefined' ? '' : $parent.vehicleDetails.bikeType.fi }}</div>
                            <div v-else-if="this.$parent.lang == 'en'" class="nettix_detail nettix_grey_bg tyyppi">Type: {{ $parent.vehicleDetails.bikeType === null || typeof $parent.vehicleDetails.bikeType === 'undefined' ? '' : $parent.vehicleDetails.bikeType.en }}</div>
                            
                            <div v-if="this.$parent.lang == ''" class="nettix_detail tyyppi_lisatietoja">Tyyppi/Lisätietoja: {{ $parent.vehicleDetails.bodyType === null || typeof $parent.vehicleDetails.bodyType === 'undefined' ? '' : $parent.vehicleDetails.bodyType.fi }}</div>
                            <div v-else-if="this.$parent.lang == 'en'" class="nettix_detail tyyppi_lisatietoja">Type/Additional info: {{ $parent.vehicleDetails.bodyType === null || typeof $parent.vehicleDetails.bodyType === 'undefined' ? '' : $parent.vehicleDetails.bodyType.en }}</div>
                            
                            <div v-if="this.$parent.lang == ''" class="nettix_detail nettix_grey_bg vari">Väri: {{ $parent.vehicleDetails.color === null || typeof $parent.vehicleDetails.color === 'undefined' ? '' : $parent.vehicleDetails.color.fi }}</div>
                            <div v-else-if="this.$parent.lang == 'en'" class="nettix_detail nettix_grey_bg vari">Color: {{ $parent.vehicleDetails.color === null || typeof $parent.vehicleDetails.color === 'undefined' ? '' : $parent.vehicleDetails.color.en }}</div>
                            
                            <div v-if="this.$parent.lang == ''" class="nettix_detail varin_tyyppi">Värin tyyppi: {{ $parent.vehicleDetails.colorType === null || typeof $parent.vehicleDetails.colorType === 'undefined' ? '' : $parent.vehicleDetails.colorType.fi }}</div>
                            <div v-else-if="this.$parent.lang == 'en'" class="nettix_detail varin_tyyppi">Color type: {{ $parent.vehicleDetails.colorType === null || typeof $parent.vehicleDetails.colorType === 'undefined' ? '' : $parent.vehicleDetails.colorType.en }}</div>
                            
                            <div v-if="this.$parent.lang == ''" class="nettix_detail nettix_grey_bg polttoaine">Polttoaine: {{ $parent.vehicleDetails.fuelType === null || typeof $parent.vehicleDetails.fuelType === 'undefined' ? '' : $parent.vehicleDetails.fuelType.fi }}</div>
                            <div v-else-if="this.$parent.lang == 'en'" class="nettix_detail nettix_grey_bg polttoaine">Fuel type: {{ $parent.vehicleDetails.fuelType === null || typeof $parent.vehicleDetails.fuelType === 'undefined' ? '' : $parent.vehicleDetails.fuelType.en }}</div>
                            
                            <div v-if="this.$parent.lang == ''" class="nettix_detail vetotapa">Vetotapa: {{ $parent.vehicleDetails.driveType === null || typeof $parent.vehicleDetails.driveType === 'undefined' ? '' : $parent.vehicleDetails.driveType.fi }}</div>
                            <div v-else-if="this.$parent.lang == 'en'" class="nettix_detail vetotapa">Traction: {{ $parent.vehicleDetails.driveType === null || typeof $parent.vehicleDetails.driveType === 'undefined' ? '' : $parent.vehicleDetails.driveType.en }}</div>

                            <div v-if="this.$parent.lang == '' && $parent.vehicleDetails.accessories" class="nettix_detail nettix_grey_bg lisavarusteet">Lisävarusteet:
                                <span v-for="i in $parent.vehicleDetails.accessories" :key="i.fi">
                                {{ i.fi + ', '}}
                                </span>
                            </div>

                            <div v-else-if="this.$parent.lang == 'en' && $parent.vehicleDetails.accessories" class="nettix_detail nettix_grey_bg lisavarusteet">Accessories:
                                <span v-for="i in $parent.vehicleDetails.accessories" :key="i.en">
                                {{ i.en + ', '}}
                                </span>
                            </div>

                            <div v-if="this.$parent.lang == ''" class="nettix_detail moottorin_tilavuus">Moottorin tilavuus: {{ $parent.vehicleDetails.engineSize === null || typeof $parent.vehicleDetails.engineSize === 'undefined' ? '' : $parent.vehicleDetails.engineSize }}L</div>
                            <div v-else-if="this.$parent.lang == 'en'" class="nettix_detail moottorin_tilavuus">Engine capacity: {{ $parent.vehicleDetails.engineSize === null || typeof $parent.vehicleDetails.engineSize === 'undefined' ? '' : $parent.vehicleDetails.engineSize }}L</div>
                            
                            <div v-if="this.$parent.lang == ''" class="nettix_detail nettix_grey_bg omistajien_lkm">Omistajien lukumäärä: {{ $parent.vehicleDetails.totalOwners === null || typeof $parent.vehicleDetails.totalOwners === 'undefined' ? '' : $parent.vehicleDetails.totalOwners }}kpl</div>
                            <div v-else-if="this.$parent.lang == 'en'" class="nettix_detail nettix_grey_bg omistajien_lkm">Number of owners: {{ $parent.vehicleDetails.totalOwners === null || typeof $parent.vehicleDetails.totalOwners === 'undefined' ? '' : $parent.vehicleDetails.totalOwners }}pcs</div>
                            
                            <div v-if="this.$parent.lang == ''" class="nettix_detail mittarilukema">Mittarilukema: {{ $parent.vehicleDetails.kilometers === null || typeof $parent.vehicleDetails.kilometers === 'undefined' ? '' : $parent.vehicleDetails.kilometers }}km</div>
                            <div v-else-if="this.$parent.lang == 'en'" class="nettix_detail mittarilukema">Mileage: {{ $parent.vehicleDetails.kilometers === null || typeof $parent.vehicleDetails.kilometers === 'undefined' ? '' : $parent.vehicleDetails.kilometers }}km</div>

                            <div v-if="this.$parent.lang == ''" @click="takaisin_hakutuloksiin()" v-scroll-to="'#nettix_hakutulokset_tag'" class="nettix_takaisin_hakutuloksiin">&laquo; Takaisin hakutuloksiin</div>
                            <div v-else-if="this.$parent.lang == 'en'" @click="takaisin_hakutuloksiin()" v-scroll-to="'#nettix_hakutulokset_tag'" class="nettix_takaisin_hakutuloksiin">&laquo; Back to search results</div>
                        </div>
                    </div>
                </div>
                <div class="wb-md-4" v-if="this.$parent.asetukset.mainos == 'kylla' || this.$parent.asetukset.lisatiedot == 'kylla' || this.$parent.asetukset.viesti == 'kylla' || this.$parent.asetukset.sijainti == 'kylla' || this.$parent.asetukset.tiedot_email == 'kylla' || this.$parent.asetukset.laskuri == 'kylla'">
                    <div id="nettix_laskuri" class="nettix_sidebar" v-if="this.$parent.asetukset.laskuri == 'kylla'">
                        <Laskuri></Laskuri>
                    </div>
                    <div id="nettix_mainos" class="nettix_sidebar" v-if="this.$parent.asetukset.mainos == 'kylla'">
                        <h3>{{ this.$parent.asetukset.mainosteksti }}</h3>
                        
                        <a v-if="this.$parent.lang == ''" style="text-decoration: none;" :href="this.$parent.asetukset.mainos_linkki"><div id="nettix_haku_btn">Pyydä lisätietoja!</div></a>
                        <a v-else-if="this.$parent.lang == 'en'" style="text-decoration: none;" :href="this.$parent.asetukset.mainos_linkki"><div id="nettix_haku_btn">Ask for more info!</div></a>
                    </div>
                    <div id="nettix_tiedot_email" class="nettix_sidebar" v-if="this.$parent.asetukset.tiedot_email == 'kylla'">
                        <h3 v-if="this.$parent.lang == ''">Tilaa ajoneuvon tiedot sähköpostiin:</h3>
                        <h3 v-else-if="this.$parent.lang == 'en'">Get vehicle info to your email:</h3>
                        
                        <input v-if="this.$parent.lang == ''" id="nettix_tiedot_email_address" class="nettix_input" type="text" placeholder="Sähköposti*">
                        <input v-else-if="this.$parent.lang == 'en'" id="nettix_tiedot_email_address" class="nettix_input" type="text" placeholder="Email*">
                        <input id="nettix_contact_phone_tiedot_email" class="nettix_input" type="hidden">

                        <div id="nettix_viesti_errors_email_address">
                            <p v-if="this.$parent.lang == ''">Viestin lähetys ei onnistunut seuraavien virheiden takia:</p>
                            <p v-else-if="this.$parent.lang == 'en'">Message could not be delivered because of the following errors:</p>
                            
                            <div v-if="this.$parent.lang == ''" id="nettix_virhe_email_address">Sähköposti on virheellinen.</div>
                            <div v-else-if="this.$parent.lang == ''" id="nettix_virhe_email_address">Error in email field.</div>
                        </div>

                        <div @click="nettixTiedotEmail()" id="nettix_haku_btn" :class="this.odottaa_lahetysta_tiedot ? 'btn_nettix_odottaa' : ''">{{ this.laheta_nettix_tiedot }}</div>
                        
                        <div v-if="this.$parent.lang == ''" id="nettix_kiitos_viestista_tiedot_email">Tiedot lähetetty onnistuneesti!</div>
                        <div v-else-if="this.$parent.lang == 'eb'" id="nettix_kiitos_viestista_tiedot_email">Vehicle information has been delivered succesfully!</div>
                    </div>
                    <div id="nettix_yhteydenotto_lomake" class="nettix_sidebar" v-if="this.$parent.asetukset.lisatiedot == 'kylla'">
                        <h3 v-if="this.$parent.lang == ''">Kysy lisätietoja:</h3>
                        <h3 v-else-if="this.$parent.lang == 'en'">For more info:</h3>
 
                        <a v-if="this.$parent.lang == ''" class="nettix_contact" @click="nettixGaWA()" :href="'https://api.whatsapp.com/send?phone=' + this.$parent.asetukset.whatsapp_numero +'&text=Olen%20kiinnostunut%20autosta%20' + this.$parent.vehicleDetails.registerNumber "><div class="nettix_phone">Lähetä WhatsApp -viesti</div></a>
                        <a v-else-if="this.$parent.lang == 'en'" class="nettix_contact" @click="nettixGaWA()" :href="'https://api.whatsapp.com/send?phone=' + this.$parent.asetukset.whatsapp_numero+'&text=I%20am%20interested%20of%20the%20car%20' + this.$parent.vehicleDetails.registerNumber"><div class="nettix_phone">Send WhatsApp message</div></a>
                        
                        <a v-if="this.$parent.lang == ''" class="nettix_contact" @click="nettixGaTel()" :href="'tel:' + this.$parent.asetukset.yrityksen_puhelin"><div class="nettix_phone">Soita: {{ this.$parent.asetukset.yrityksen_puhelin }}</div></a>
                        <a v-else-if="this.$parent.lang == 'en'" class="nettix_contact" @click="nettixGaTel()" :href="'tel:' + this.$parent.asetukset.yrityksen_puhelin"><div class="nettix_phone">Call: {{ this.$parent.asetukset.yrityksen_puhelin }}</div></a>
                        
                        <a v-if="this.$parent.lang == ''" class="nettix_contact" @click="nettixGaMailto()" :href="'mailto:' + this.$parent.asetukset.yrityksen_email"><div class="nettix_email">Sähköposti: {{ this.$parent.asetukset.yrityksen_email }}</div></a>
                        <a v-else-if="this.$parent.lang == 'en'" class="nettix_contact" @click="nettixGaMailto()" :href="'mailto:' + this.$parent.asetukset.yrityksen_email"><div class="nettix_email">Email: {{ this.$parent.asetukset.yrityksen_email }}</div></a>
                    </div>
                    <div id="nettix_yhteydenotto" class="nettix_sidebar" v-if="this.$parent.asetukset.viesti == 'kylla'">
                        <h3 v-if="this.$parent.lang == ''">Viesti:</h3>
                        <h3 v-else-if="this.$parent.lang == 'en'">Message:</h3>
                        <div id="nettix_viesti_errors">
                            <p v-if="this.$parent.lang == ''">Viestin lähetys ei onnistunut seuraavien virheiden takia:</p>
                            <p v-else-if="this.$parent.lang == 'en'">Message could not be delivered because of the following errors:</p>
                            
                            <div v-if="this.$parent.lang == ''" id="nettix_virhe_nimi">Nimi puuttuu tai on virheellinen.</div>
                            <div v-else-if="this.$parent.lang == ''" id="nettix_virhe_nimi">Error in name field.</div>
                            
                            <div v-if="this.$parent.lang == ''" id="nettix_virhe_email">Sähköposti on virheellinen.</div>
                            <div v-else-if="this.$parent.lang == ''" id="nettix_virhe_email">Error in email field.</div>

                            <div v-if="this.$parent.lang == ''" id="nettix_virhe_puhelin">Puhelinnumero on virheellinen.</div>
                            <div v-else-if="this.$parent.lang == ''" id="nettix_virhe_puhelin">Error in phone number field.</div>
                            
                            <div v-if="this.$parent.lang == ''" id="nettix_virhe_viesti">Viestikenttä on tyhjä.</div>
                            <div v-else-if="this.$parent.lang == ''" id="nettix_virhe_viesti">Error in message field.</div>
                        </div>
                        <input v-if="this.$parent.lang == ''" id="nettix_contact_nimi" class="nettix_input" type="text" placeholder="Nimi*">
                        <input v-else-if="this.$parent.lang == 'en'" id="nettix_contact_nimi" class="nettix_input" type="text" placeholder="Name*">
                        
                        <input v-if="this.$parent.lang == ''" id="nettix_contact_email" class="nettix_input" type="text" placeholder="Sähköposti*">
                        <input v-if="this.$parent.lang == 'en'" id="nettix_contact_email" class="nettix_input" type="text" placeholder="Email*">
                        
                        <input v-if="this.$parent.lang == ''" id="nettix_contact_puhelin" class="nettix_input" type="text" placeholder="Puhelin">
                        <input v-if="this.$parent.lang == 'en'" id="nettix_contact_puhelin" class="nettix_input" type="text" placeholder="Phone number">
                        
                        <input id="nettix_contact_phone" class="nettix_input" type="hidden">
                        
                        <textarea v-if="this.$parent.lang == ''" id="nettix_contact_viesti" class="nettix_input" placeholder="Viesti*"></textarea>
                        <textarea v-else-if="this.$parent.lang == ''" id="nettix_contact_viesti" class="nettix_input" placeholder="Message*"></textarea>
                        
                        <div @click="nettixContact()" id="nettix_haku_btn" :class="this.odottaa_lahetysta ? 'btn_nettix_odottaa' : ''">{{ this.laheta_nettix }}</div>
                        
                        <div v-if="this.$parent.lang == ''" id="nettix_kiitos_viestista">Viesti lähetetty onnistuneesti!</div>
                        <div v-else-if="this.$parent.lang == 'en'" id="nettix_kiitos_viestista">Message has been delivered succesfully!</div>
                    </div>
                    <div id="nettix_sijainti" class="nettix_sidebar" v-if="this.$parent.asetukset.sijainti == 'kylla'">
                        <h3 v-if="this.$parent.lang == ''">Sijainti</h3>
                        <h3 v-else-if="this.$parent.lang == 'en'">Location</h3>

                        <div v-if="this.$parent.vehicleDetails.town.id == this.$parent.asetukset.paikkakunta2">
                            <p>{{ this.$parent.asetukset.yrityksen_nimi }}<br>
                                {{ this.$parent.asetukset.yrityksen_osoite2 }}<br>
                                {{ this.$parent.asetukset.yrityksen_postinumero2 }} {{ this.$parent.asetukset.yrityksen_paikkakunta2 }}</p>
                            <div><iframe :src="this.$parent.asetukset.googlemaps2" width="100%" height="300" frameborder="0" style="border:0" allowfullscreen></iframe></div>
                        </div>

                        <div v-else>
                            <p>{{ this.$parent.asetukset.yrityksen_nimi }}<br>
                                {{ this.$parent.asetukset.yrityksen_osoite }}<br>
                                {{ this.$parent.asetukset.yrityksen_postinumero }} {{ this.$parent.asetukset.yrityksen_paikkakunta }}</p>
                            <div><iframe :src="this.$parent.asetukset.googlemaps" width="100%" height="300" frameborder="0" style="border:0" allowfullscreen></iframe></div>
                        </div>
                        
                    </div>
                    <div id="nettix_jakonapit" v-if="this.$parent.asetukset.jakonapit == 'kylla'">
                        <div id="share-buttons">

                            <!-- Facebook -->
                            <a :href="'http://www.facebook.com/sharer.php?u=' + this.encodedurl" target="_blank">
                                <img :src="this.$parent.siteurl + '/wp-content/plugins/wb-nettix/dist/images/facebook.png'" alt="Facebook" />
                            </a>

                            <!-- Google+ -->
                            <a :href="'https://plus.google.com/share?url=' + this.encodedurl" target="_blank">
                                <img :src="this.$parent.siteurl + '/wp-content/plugins/wb-nettix/dist/images/google.png'" alt="Google" />
                            </a>

                            <!-- LinkedIn -->
                            <a :href="'http://www.linkedin.com/shareArticle?mini=true&amp;url=' + this.encodedurl" target="_blank">
                                <img :src="this.$parent.siteurl + '/wp-content/plugins/wb-nettix/dist/images/linkedin.png'" alt="LinkedIn" />
                            </a>

                            <!-- Twitter -->
                            <a class="twitter-share-button" data-size="large" :href="'https://twitter.com/intent/tweet?url=' + this.encodedurl" target="_blank">
                                <img :src="this.$parent.siteurl + '/wp-content/plugins/wb-nettix/dist/images/twitter.png'" alt="Twitter" />
                            </a>

                        </div>
                    </div>
                </div>
            </div>
            <div id="nettix_muut_ajoneuvot_wrap" class="nettix_muut_ajoneuvot_wrap">
                <div class="row">
                    <div class="wb-md-12" style="margin: 0 auto;">
                        <h2 v-if="this.$parent.lang == ''">Muita liikkeemme ajoneuvoja: </h2>
                        <h2 v-else-if="this.$parent.lang == 'en'">Other similar vehicles: </h2>

                        <slick ref="slick3" :options="slickOptions3">
                            <div v-for="vehicle in this.$parent.ajoneuvot" :key="vehicle.id">
                                <div @click="additionalInfo(vehicle.id)" v-scroll-to="'#nettix_hakutulokset_tag'" class="nettix_thumb">
                                    <img style="margin: 0 auto;" :src="vehicle.images[0] === null || typeof vehicle.images[0] === 'undefined' ? $parent.defaultImg : vehicle.images[0].medium.url">
                                </div>
                            </div>
                        </slick>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios'
    import Qs from 'qs'
    import Slick from 'vue-slick'
    import Laskuri from './Laskuri.vue'

    export default {
        components: {
            Slick,
            Laskuri
        },
        created: function () {
            this.$parent.ajoneuvonTiedot(),
            this.setUpSocialData()
        },
        data: function(){
            return {
                slickOptions: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    asNavFor: '.slider-nav',
                    arrows: false,
                    dots: false,
                    fade: true,
                    adaptiveHeight: false
                },
                slickOptions2: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    asNavFor: '.slider-for',
                    dots: false,
                    centerMode: true,
                    focusOnSelect: true
                },
                slickOptions3: {
                    autoplay: true,
                    slidesToShow: 5,
                    slidesToScroll: 5,
                    dots: false,
                    responsive: [
                            {
                              breakpoint: 992,
                              settings: {
                                slidesToShow: 3,
                                slidesToScroll: 1
                              }
                            },
                            {
                              breakpoint: 400,
                              settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                              }
                            }
                    ]
                },
                laheta_nettix: 'Lähetä',
                laheta_nettix_tiedot: 'Lähetä',
                odottaa_lahetysta: false,
                odottaa_lahetysta_tiedot: false,
                currentUrl: window.location.href,
                encodedurl: '',
                vehicleData: '',
                vehicleImg: ''
            }
        },
        head: {
            title: function () {
                return {
                    inner: this.vehicleData
                }
            },
        },

        beforeUpdate() {
            if (this.$refs.slick) {
                this.$refs.slick.destroy()
            }
            if (this.$refs.slick2) {
                this.$refs.slick2.destroy()
            }
            if (this.$refs.slick3) {
                this.$refs.slick3.destroy()
            }
        },
        updated() {
            this.$nextTick(function () {
                if (this.$refs.slick) {
                    this.$refs.slick.create(this.slickOptions)
                }

                if (this.$refs.slick2) {
                    this.$refs.slick2.create(this.slickOptions2)
                }
                if (this.$refs.slick3) {
                    this.$refs.slick3.create(this.slickOptions3)
                }
            });

            this.getAsyncData()
        },
        methods: {
            getAsyncData: function () {
                var self = this
                var setData = ''
                var setImg = ''

                if(this.$parent.vehicleDetails.make.name != null && typeof this.$parent.vehicleDetails.make.name != 'undefined' && this.$parent.vehicleDetails.model.name != null && typeof this.$parent.vehicleDetails.model.name != 'undefined') {
                    var setDAta = this.$parent.vehicleDetails.make.name + ' ' +  this.$parent.vehicleDetails.model.name
                }

                if(this.$parent.vehicleDetails.images != null && this.$parent.vehicleDetails.images != '' && typeof this.$parent.vehicleDetails.images != 'undefined' && this.$parent.vehicleDetails.images.length > 0 ) {
                    var setImg = this.$parent.vehicleDetails.images[0].large.url
                }

                window.setTimeout(function () {
                    self.title = setDAta
                    self.vehicleData = setDAta
                    self.vehicleImg = setImg
                    self.$emit('updateHead')
                }, 500)

                this.$parent.loader = false
            },
            imagestyles(url) {
                return { 'background-image': 'url(' + url + ')' }
            },
            additionalInfo(id) {
                if(this.$parent.asetukset.cpt == 'kylla') {
                    this.$parent.haeSinglesivu(id)
                } else {
                    this.$parent.view = 'single'
                    this.$parent.singleid = id

                    window.history.pushState("", "", "?id=" + id + "&tyyppi=" + this.$parent.ajoneuvotyyppi)

                    this.$parent.ajoneuvonTiedot()
                }
                
                if(this.$parent.asetukset.ga == 'kylla' && typeof ga != 'undefined') {
                    ga('send', 'event', 'click', 'Nettix vehicle details button clicked with id: ' + id)
                }
            },
            takaisin_hakutuloksiin() {
                var pagenumber = this.$parent.pagenumber

                this.$parent.changePage(pagenumber)
            },
            setUpSocialData() {
                this.encodedurl = encodeURIComponent(this.currentUrl)

                if(this.$parent.lang == 'en') {
                    this.laheta_nettix = 'Send'
                    this.laheta_nettix_tiedot = 'Send'
                } else {
                    this.laheta_nettix = 'Lähetä'
                    this.laheta_nettix_tiedot = 'Lähetä'
                }
            },
            isEmail(email) {
                var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                return regex.test(email)
            },
            isPhone(phone) {
                var regex = /^[0-9+ ]+$/;
                return regex.test(phone)
            },
	        nettixGaWA() {
                if(this.$parent.asetukset.ga == 'kylla' && typeof ga != 'undefined') {
                    ga('send', 'event', 'Nettix yhteydenotto', 'Whatsapp', this.currentUrl)
                }
            },
            nettixGaTel() {
                if(this.$parent.asetukset.ga == 'kylla' && typeof ga != 'undefined') {
                    ga('send', 'event', 'Nettix yhteydenotto', 'Soitto', this.currentUrl)
                }
            },
            nettixGaMailto() {
                if(this.$parent.asetukset.ga == 'kylla' && typeof ga != 'undefined') {
                    ga('send', 'event', 'Nettix yhteydenotto', 'Sähköpostin lähetys', this.currentUrl)
                }
            },
            nettixContact() {
                var error = false

                document.getElementById('nettix_viesti_errors').style.display = 'none'
                document.getElementById('nettix_virhe_nimi').style.display = 'none'
                document.getElementById('nettix_virhe_email').style.display = 'none'
                document.getElementById('nettix_virhe_puhelin').style.display = 'none'
                document.getElementById('nettix_virhe_viesti').style.display = 'none'

                var obj = {}
                var nettix_contact_nimi = document.getElementById('nettix_contact_nimi').value
                var nettix_contact_email = document.getElementById('nettix_contact_email').value
                var nettix_contact_puhelin = document.getElementById('nettix_contact_puhelin').value
                var nettix_contact_viesti = document.getElementById('nettix_contact_viesti').value
                var getUrl = window.location.href;

                // Hunajakannu
                var nettix_contact_phone = document.getElementById('nettix_contact_phone').value

                var isemailcorrect = this.isEmail(nettix_contact_email)
                var isphonecorrect = this.isPhone(nettix_contact_puhelin)

                if(nettix_contact_nimi == '') {
                    error = true

                    document.getElementById('nettix_viesti_errors').style.display = 'block'
                    document.getElementById('nettix_virhe_nimi').style.display = 'block'
                }

                if(nettix_contact_email == '' || !isemailcorrect) {
                    error = true

                    document.getElementById('nettix_viesti_errors').style.display = 'block'
                    document.getElementById('nettix_virhe_email').style.display = 'block'
                }

                if(nettix_contact_puhelin !== '' && !isphonecorrect) {
                    error = true

                    document.getElementById('nettix_viesti_errors').style.display = 'block'
                    document.getElementById('nettix_virhe_puhelin').style.display = 'block'
                }

                if(nettix_contact_viesti == '') {
                    error = true

                    document.getElementById('nettix_viesti_errors').style.display = 'block'
                    document.getElementById('nettix_virhe_viesti').style.display = 'block'
                }

                if(!error) {
                    obj['nettix_contact_nimi'] = nettix_contact_nimi
                    obj['nettix_contact_email'] = nettix_contact_email
                    obj['nettix_contact_puhelin'] = nettix_contact_puhelin
                    obj['nettix_contact_viesti'] = nettix_contact_viesti
                    obj['nettix_contact_url'] = getUrl

                    var sendDataAjax = JSON.stringify(obj);

                    var data = {
                        'action': 'sendMail',
                        'security': wb_nettixAdminAjax.security,
                        'sendData': sendDataAjax
                    }
                    
                    var vm = this;

                    var url = wb_nettixAdminAjax.ajaxurl
                    this.odottaa_lahetysta = true
                    
                    if(this.lang == 'en') {
                        this.laheta_nettix = 'Please wait...'
                    } else {
                        this.laheta_nettix = 'Odota hetki...'
                    }

                    axios.post(url, Qs.stringify(data))
                        .then(function (response) {
                            vm.odottaa_lahetysta = false

                            if(vm.$parent.asetukset.ga == 'kylla' && typeof ga != 'undefined') {
                                ga('send', 'event', 'Nettix yhteydenotto', 'Yhteydenottolomake', vm.currentUrl)
                            }

                            if(vm.lang == 'en') {
                                vm.laheta_nettix = 'Send'
                            } else {
                                vm.laheta_nettix = 'Lähetä'
                            }

                            document.getElementById('nettix_kiitos_viestista').style.display = 'block'

                            document.getElementById('nettix_contact_nimi').value = ''
                            document.getElementById('nettix_contact_email').value = ''
                            document.getElementById('nettix_contact_puhelin').value = ''
                            document.getElementById('nettix_contact_viesti').value = ''

                            setTimeout(function(){
                                document.getElementById('nettix_kiitos_viestista').style.display = 'none'
                            }, 3000)
                        }).catch(function (error) {
                        console.log(error)
                    });
                }
            },
            nettixTiedotEmail() {
                var error = false

                document.getElementById('nettix_viesti_errors_email_address').style.display = 'none'

                var obj = {}
                var nettix_contact_email = document.getElementById('nettix_tiedot_email_address').value
                var getUrl = window.location.href;

                // Hunajakannu
                var nettix_contact_phone = document.getElementById('nettix_contact_phone_tiedot_email').value

                var isemailcorrect = this.isEmail(nettix_contact_email)

                if(nettix_contact_email == '' || !isemailcorrect) {
                    error = true

                    document.getElementById('nettix_viesti_errors_email_address').style.display = 'block'
                    document.getElementById('nettix_virhe_email_address').style.display = 'block'
                }

                if(!error) {
                    var description = JSON.stringify(this.$parent.vehicleDetails.description);
                    description = description.replace(/"/g, '');

                    obj['nettix_contact_email'] = nettix_contact_email
                    obj['make'] = this.$parent.vehicleDetails.make
                    obj['model'] = this.$parent.vehicleDetails.model

                    obj['description'] = description
                   
                    obj['year'] = this.$parent.vehicleDetails.year
                    obj['price'] = this.$parent.vehicleDetails.price

                    if(this.$parent.vehicleDetails.bikeType) {
                        obj['bikeType'] = this.$parent.vehicleDetails.bikeType
                    } else {
                        obj['bikeType'] = ''
                    }
                    
                    obj['bodyType'] = this.$parent.vehicleDetails.bodyType
                    obj['color'] = this.$parent.vehicleDetails.color
                    obj['colorType'] = this.$parent.vehicleDetails.colorType
                    
                    obj['fuelType'] = this.$parent.vehicleDetails.fuelType
                    obj['driveType'] = this.$parent.vehicleDetails.driveType
                    obj['engineSize'] = this.$parent.vehicleDetails.engineSize
                    obj['totalOwners'] = this.$parent.vehicleDetails.totalOwners
                    obj['kilometers'] = this.$parent.vehicleDetails.kilometers
                     
                    obj['accessories'] = this.$parent.vehicleDetails.accessories
                    
                    obj['lang'] = this.$parent.lang
                    

                    var sendDataAjax = JSON.stringify(obj);

                    var data = {
                        'action': 'sendMailVehicleInfo',
                        'security': wb_nettixAdminAjax.security,
                        'sendData': sendDataAjax
                    }

                    var vm = this;

                    var url = wb_nettixAdminAjax.ajaxurl
                    this.odottaa_lahetysta_tiedot = true
                    
                    if(this.lang == 'en') {
                        this.laheta_nettix_tiedot = 'Please wait...'
                    } else {
                        this.laheta_nettix_tiedot = 'Odota hetki...'
                    }

                    axios.post(url, Qs.stringify(data))
                        .then(function (response) {
                            vm.odottaa_lahetysta_tiedot = false

                            if(vm.$parent.asetukset.ga == 'kylla' && typeof ga != 'undefined') {
                                ga('send', 'event', 'Nettix ajoneuvojen tiedot', 'Ajoneuvojen tiedot kenttä', vm.currentUrl)
                            }

                            if(vm.lang == 'en') {
                                vm.laheta_nettix_tiedot = 'Send'
                            } else {
                                vm.laheta_nettix_tiedot = 'Lähetä'
                            }

                            document.getElementById('nettix_kiitos_viestista_tiedot_email').style.display = 'block'

                            document.getElementById('nettix_tiedot_email_address').value = ''

                            setTimeout(function(){
                                document.getElementById('nettix_kiitos_viestista_tiedot_email').style.display = 'none'
                            }, 3000)
                        }).catch(function (error) {
                        console.log(error)
                    });
                }
            }
        }
    }
</script>
