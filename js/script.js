import Vue from 'vue'
import App from './components/Nettix.vue'
import VueHead from 'vue-head'
import vueScrollTo from 'vue-scroll-to'
 
window.onload = function () {
    new Vue({
        el: '#nettix',
        render: h => h(App)
    });  
}

Vue.use(VueHead)
Vue.use(vueScrollTo)