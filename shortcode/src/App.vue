/* eslint-disable */
<template>
    <div class="floor-form">
        <multiselect v-model="value" :options="options" :searchable="false" :close-on-select="false" :show-labels="false"></multiselect>
    </div>
</template>

<script>
  import Multiselect from 'vue-multiselect'

  Vue.component('multiselect', Multiselect)

  export default {
    name: 'app',
    data () {
      return {
        value: 'квартира',
        options: ['квартира', 'коттедж', 'офис']
      }
    }
  }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style lang="scss">
    .floor-form {
        position: absolute;
        top: 50%;
        left: auto;
        width: 290px;
        height: 430px;
        margin: 0;
        padding: 30px;
        background-color: rgba(255, 255, 255, .9);
        box-shadow: 5px 5px 11px rgba(0, 0, 0, .1);
        transform: translateY(-50%);
    }
</style>
