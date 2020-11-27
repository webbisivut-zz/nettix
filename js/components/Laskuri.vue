<template>
  <div class="wb_nettix_calculator" v-if="this.laskuriActivated">
    <div id="wb_counter_wrap">
        <h3>Rahoituslaskuri</h3>

        <p class="wb_counter_title">Rahoitettava osuus (EUR): {{ wb_counter_price }}</p>
        <p><input class="wb_counter_input_range" type="range" min="2000" max="100000" step="100" v-model="wb_counter_price" v-on:change="lopullinenLaskelma()"></p>
        
        <p class="wb_counter_title">Käsiraha (EUR): {{ wb_counter_kasiraha }}</p>
        <p><input class="wb_counter_input_range" type="range" min="0" max="100000" step="100" v-model="wb_counter_kasiraha" v-on:change="lopullinenLaskelma()"></p>
        
        <p class="wb_counter_title">Suurempi viimeinen erä (EUR): {{ wb_counter_viimeinen_era }}</p>
        <p><input class="wb_counter_input_range" type="range" min="0" max="50000" step="100" v-model="wb_counter_viimeinen_era" v-on:change="lopullinenLaskelma()"></p>
        
        <p class="wb_counter_title">Korko: (%)</p>
        <p><input class="wb_counter_input" type="text" v-model="wb_counter_korko" v-on:change="lopullinenLaskelma()"></p>
        
        <p class="wb_counter_title">Laina-aika (kk):</p>
        <p><select v-model="wb_laina_aika" class="wb_laina_aika" v-on:change="lopullinenLaskelma()">
          <option id="wb_laina_aika_24kk">24</option>
          <option id="wb_laina_aika_48kk">48</option>
          <option id="wb_laina_aika_60kk">60</option>
          <option id="wb_laina_aika_72kk">72</option>
        </select></p>
      </div>

      <div id="wb_counted_wrap">
        <p class="wb_counter_input" v-if="!this.virhe">Kuukausierä: {{ haeKkEra }} EUR / kk*</p>
        <p class="wb_counter_input" v-if="this.wb_counter_kasiraha > this.wb_counter_price">Virhe! Käsiraha ei voi olla suurempi kuin ajoneuvon hinta!</p>
        <p class="wb_counter_input" v-if="this.wb_counter_viimeinen_era > this.wb_counter_price">Virhe! Viimeinen erä ei voi olla suurempi kuin ajoneuvon hinta!</p>
        <p class="wb_counter_info" v-if="!this.virhe">* Laskelma on suuntaa-antava. Varmista lopullinen kk-erän suuruus aina myyjältä!</p>
      </div>
  </div>
</template>

<script>
export default {
  name: 'App',
  data() {
    return {
      haeKkEra: '',
      laskuriActivated: false,
      perustamismaksu: 200,
      tilinhoitomaksu: 9,
      euribor: 3,
      wb_counter_price: 2000,
      wb_counter_kasiraha: 0,
      wb_counter_viimeinen_era: 0,
      wb_counter_korko: 3.9,
      wb_laina_aika: 72,
      wb_takaisinmaksu_yhteensa: 0,
      wb_korot_yhteensa: 0,
      virhe: false,
    }
  },
  created: function() {
      var getLaskuriActivated = document.getElementById('nettix_laskuri').innerHTML;
      
      if(getLaskuriActivated == '579094ec01ec13ea9a8a61072610c617') {
          this.laskuriActivated = true;
      }

      this.getVehiclePrice();
  },
  methods: {
    getVehiclePrice() {
      if(typeof this.$parent.$parent != 'undefined' && typeof this.$parent.$parent.vehicleDetails != 'undefined' && this.$parent.$parent.vehicleDetails.price != '' && this.$parent.$parent.vehicleDetails.price != null) {
        this.wb_counter_price = this.$parent.$parent.vehicleDetails.price;

        this.wb_counter_kasiraha = Math.ceil(this.wb_counter_price / 3);

        this.lopullinenLaskelma();
      }
    },
    lopullinenLaskelma() {
        let hinta = this.wb_counter_price;
        let kasiraha = this.wb_counter_kasiraha;
        let lainaAika = this.wb_laina_aika;
        let korko = this.wb_counter_korko;
        let viimeinen_era = this.wb_counter_viimeinen_era;

        var reg = new RegExp('^[0-9]+$');

        reg.test(hinta);
        reg.test(kasiraha);
        reg.test(lainaAika);

        this.virhe = false;

        if(this.wb_counter_kasiraha > this.wb_counter_price) {
          this.virhe = true;
        }

        if(this.wb_counter_viimeinen_era > this.wb_counter_price) {
          this.virhe = true;
        }

        if(!this.virhe && hinta || hinta == 0 && kasiraha && lainaAika) {
            this.kuukausiera(hinta, kasiraha, lainaAika, korko, viimeinen_era);
        } 
    },
    kuukausiera(hinta, kasiraha, lainaAika, korko, viimeinen_era) {
        if(viimeinen_era > 0) {
          var laskeKulut = this.todellinenVuosikorko(viimeinen_era, 1, parseFloat(korko), 0);
          var setViimenenKKEra = Math.ceil(parseFloat(laskeKulut.korot_yhteensa) + parseFloat(laskeKulut.pow));
        } else {
          var setViimenenKKEra = 0;
        }

        var setHinta = (parseFloat(hinta) + parseFloat(this.perustamismaksu)) - parseFloat(kasiraha) - parseFloat(viimeinen_era);

        var haeKKEra = this.todellinenVuosikorko(setHinta, lainaAika, parseFloat(korko), parseFloat(viimeinen_era));

        this.haeKkEra =  Math.ceil(parseFloat(haeKKEra.kk_era) + parseFloat(this.tilinhoitomaksu) + setViimenenKKEra);
    },
    todellinenVuosikorko(hinta, lainaAika, korko, viimeinen_era) {
      var principal = parseFloat(hinta);
      var interest = (korko / 100) / 12;
      var payments = lainaAika;

      // Now compute the monthly payment figure, using esoteric math.
      var x = Math.pow(1 + interest, payments);
      var monthly = (principal*x*interest)/(x-1);

      // Check that the result is a finite number. If so, display the results.
      var obj = {};

      if (!isNaN(monthly) && 
          (monthly != Number.POSITIVE_INFINITY) &&
          (monthly != Number.NEGATIVE_INFINITY)) {

            obj['kk_era'] = monthly;
            obj['pow'] = x;
            obj['hinta_yhteensa'] = Math.round(monthly * payments);
            obj['korot_yhteensa'] = Math.round((monthly * payments) - principal);
            obj['kustannukset_yhteensa'] = Math.round((monthly * payments) - principal) + (9 * payments) + 200;

          return obj;
      }
      
    }
  }
}
</script>