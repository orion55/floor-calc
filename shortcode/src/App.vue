<template>
    <div class="calc__form">
        <div v-show="info.loading" class="calc__preloader">
            <div class="calc__loader"></div>
        </div>
        <div v-show="!info.loading" class="calc__box">
            <transition name="transition-box" enter-active-class="animated zoomIn"
                        leave-active-class="animated fadeOut fast">
                <div class="calc__container" v-if="!additionalServices.show">
                    <div class="calc__head">Объект клининга</div>
                    <multiselect v-model="objectCleaning.selected" :options="objectCleaning.options"
                                 label="name" track-by="id" :searchable="false"
                                 :show-labels="false" :maxHeight="200"
                                 class="calc__dropdown calc__dropdown--object"
                                 :allow-empty="false"></multiselect>
                    <div v-if="objectCleaning.selected.id === 0">
                        <div class="calc__head">Количество комнат</div>
                        <multiselect v-model="numberOfRooms.selected" :options="numberOfRooms.options"
                                     label="name" track-by="id" :searchable="false"
                                     :show-labels="false" :maxHeight="200"
                                     class="calc__dropdown calc__dropdown&#45;&#45;number"
                                     :allow-empty="false"></multiselect>
                    </div>
                    <div v-else>
                        <div class="calc__head">Площадь уборки</div>
                        <div class="calc__area">
                            <input type="number" class="calc__input" min="40" max="220" v-model="cleaningArea.value">
                            <span class="calc__sup">м<sup><small>2</small></sup></span>
                        </div>
                    </div>
                    <div class="calc__head">Периодичность уборки</div>
                    <multiselect v-model="periodicity.selected" :options="periodicity.options"
                                 label="name" track-by="id" :searchable="false"
                                 :show-labels="false" :maxHeight="200"
                                 class="calc__dropdown calc__dropdown--periodicity"
                                 :allow-empty="false"></multiselect>
                    <div class="calc__head">Тип уборки</div>
                    <multiselect v-model="cleaningType.selected" :options="cleaningType.options"
                                 label="name" track-by="id" :searchable="false"
                                 :show-labels="false" :maxHeight="200"
                                 class="calc__dropdown calc__dropdown--cleaning"
                                 :allow-empty="false"></multiselect>
                </div>
            </transition>
            <div :class="[{'calc__margin-top': !additionalServices.show}, 'calc__services']"
                 @click.prevent="additionalServices.show = !additionalServices.show">
                Дополнительные услуги <span class="calc__icon" v-if="!additionalServices.show">&#43;</span>
                <span class="calc__icon" v-else>&times;</span>
            </div>
            <transition name="transition-box" enter-active-class="animated fadeIn delay-1s">
                <div class="calc__services-list" v-if="additionalServices.show">
                    <label class="control control-checkbox calc__label" v-for="item in additionalServices.data"
                           :key="item.id" @click="changeIndex(item.id)">
                        {{ item.name }}
                        <input type="checkbox" v-if="additionalServices.checkedIndex[item.id]" checked>
                        <input type="checkbox" v-else>
                        <div class="control_indicator"></div>
                    </label>
                </div>
            </transition>
            <div class="calc__holder">
                <div class="calc__price">от <span class="calc__sum">{{animatedNumber}}</span> ₽</div>
                <button type="button" class="btn btn--result hvr-pop"
                        @click.prevent="">Заказать
                </button>
            </div>
        </div>
    </div>
</template>

<script>
  import Multiselect from 'vue-multiselect'
  import VueResource from 'vue-resource'
  import { TweenLite } from 'gsap'

  var _ = require('lodash')

  Vue.component('multiselect', Multiselect)
  Vue.use(VueResource)

  export default {
    name: 'app',
    data () {
      return {
        info: {
          data: null,
          loading: true,
          errored: false,
        },
        objectCleaning: {
          selected: {},
          options: []
        },
        numberOfRooms: {
          selected: {},
          options: []
        },
        periodicity: {
          selected: {},
          options: []
        },
        cleaningType: {
          selected: {},
          options: []
        },
        cleaningArea: {
          value: 40,
          price: 0
        },
        additionalServices: {
          show: false,
          data: [],
          checkedIndex: []
        },
        tweenedNumber: 0
      }
    },
    computed: {
      sum: function () {
        let price = 0

        if (!_.isEmpty(this.info.data)) {
          price += +this.objectCleaning.selected.price

          if (+this.objectCleaning.selected.id === 0) {
            price += +this.numberOfRooms.selected.price
          } else {
            if (this.cleaningArea.value > 0) {
              price += this.cleaningArea.value * this.cleaningArea.price
            }
          }

          price += +this.periodicity.selected.price

          price += +this.cleaningType.selected.price

          this.additionalServices.checkedIndex.forEach((item, i) => {
            if (item) {
              price += +this.additionalServices.data[i].price
            }
          })
        }

        return price
      },
      animatedNumber: function () {
        return this.tweenedNumber.toFixed(0)
      }
    },
    watch: {
      sum: function (newValue) {
        TweenLite.to(this.$data, 1, {tweenedNumber: newValue})
      }
    },
    mounted () {
      this.$http.get(wp_data.plugin_dir_url + 'json/price.json')
        .then(response => {
          this.info.data = response.body

          //Заполняем список объектов уборки
          _.forEach(this.info.data.objectCleaning, (item) => {
            this.objectCleaning.options.push(item)
          })
          this.objectCleaning.selected = this.objectCleaning.options[0]

          //Заполняем список количество комнат
          _.forEach(this.info.data.numberOfRooms, (item) => {
            this.numberOfRooms.options.push(item)
          })
          this.numberOfRooms.selected = this.numberOfRooms.options[0]

          //Заполняем список периодичность уборки
          _.forEach(this.info.data.periodicity, (item) => {
            this.periodicity.options.push(item)
          })
          this.periodicity.selected = this.periodicity.options[0]

          //Заполняем список периодичность уборки
          _.forEach(this.info.data.cleaningType, (item) => {
            this.cleaningType.options.push(item)
          })
          this.cleaningType.selected = this.cleaningType.options[0]

          //устанавливаем цену уборки за квадратный метр
          this.cleaningArea.price = +this.info.data.cleaningArea.price

          //устанавливаем дополнительные услуги
          this.additionalServices.data = this.info.data.additionalServices
          this.additionalServices.checkedIndex = Array(this.additionalServices.data.length).fill(false)
          // console.log(this.additionalServices.checkedIndex)

          this.info.loading = false
        }, error => {
          this.info.loading = false
          console.error(error)
        })
    },
    methods: {
      log: function (e) {
        console.log(e)
      },
      changeIndex: _.debounce(function (index) {
        this.additionalServices.checkedIndex.splice(index, 1, !this.additionalServices.checkedIndex[index])
      }, 50)
    }
  }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style src="animate.css/animate.min.css"></style>

<style lang="scss">
    $color-main: #F25E99;
    $color-two: darken($color-main, 10%);

    #floor-calc {
        .calc__form {
            position: absolute;
            top: 50%;
            left: 15%;
            width: 290px;
            height: 430px;
            margin: 0;
            padding: 15px;
            background-color: rgba(255, 255, 255, .9);
            box-shadow: 5px 5px 11px rgba(0, 0, 0, .1);
            transform: translateY(-50%);
            z-index: 100;
        }

        .calc__loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid $color-main;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        .calc__preloader {
            display: flex;
            flex: 1;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .multiselect__content {
            padding-left: 0;
        }

        .multiselect {
            min-height: 33px;
        }

        .multiselect__tags {
            min-height: 33px;
            padding: 3px 33px 0 5px;
            border: 1px solid rgba(60, 60, 60, .26);
        }

        .multiselect__select {
            height: 33px;
        }

        .multiselect__content-wrapper {
            z-index: 40;
        }

        .multiselect__element {
            margin-bottom: 0;
            z-index: 100;
        }

        .multiselect__option--selected.multiselect__option--highlight {
            background: $color-two;
        }

        .multiselect__content {
            margin: 0;
            list-style: none;
            display: inline-block;
            z-index: 100;
        }

        .multiselect__option--highlight {
            background: $color-main;
        }

        .multiselect__select {
            top: 2px;
        }

        .multiselect__single {
            margin-bottom: 0;
            padding-top: 3px;
            color: $color-main;
        }

        .calc__head {
            padding: 4px 4px 4px 0;
            font-size: 14px;
        }

        .btn, button.btn {
            display: inline-block;
            text-align: center;
            text-decoration: none;

            margin: 2px 0;

            color: #FFFFFF;
            background-color: $color-main;

            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 0 28px;

            line-height: 40px;
            height: 40px;

            border-radius: 20px;
            transition: all .2s ease;
            border: 0;
            outline: 0;
        }

        .btn:active {
            transform: translateY(1px);
            filter: saturate(150%);
        }

        .btn:hover {
            background-color: $color-two;
        }

        .btn::-moz-focus-inner {
            border: none;
        }

        .btn:focus {
            outline: none;
        }

        button.btn--result {
            font-family: Geometria, Arial, sans-serif;
            font-size: 18px;
            background-color: $color-two;
            color: white;
            height: 50px;
            width: 100%;
            /*margin-top: auto;*/
            box-shadow: 10px 11px 50px -13px rgba(0, 0, 0, 0.75);
        }
        .calc__box {
            display: flex;
            align-items: left;
            justify-content: flex-start;
            flex-direction: column;
            height: 100%;
            font-family: Geometria, Arial, sans-serif;
        }
        @keyframes hvr-pop {
            50% {
                transform: scale(1.05);
            }
        }
        .hvr-pop {
            display: inline-block;
            vertical-align: middle;
            transform: perspective(1px) translateZ(0);
            box-shadow: 0 0 1px rgba(0, 0, 0, 0);
        }
        .hvr-pop:hover, .hvr-pop:focus, .hvr-pop:active {
            animation-name: hvr-pop;
            animation-duration: 0.4s;
            animation-timing-function: linear;
            animation-iteration-count: 1;
        }
        .calc__services {
            font-size: 15px;
            padding: 7px 10px;
            border: #CDCDCD 1px solid;
            border-radius: 7px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: left;
            transition: all .4s ease;
            background-color: #fff;
            &:hover {
                border: 1px solid $color-main;
                color: $color-main;
            }
        }
        .calc__margin-top {
            margin-top: 7px;
        }
        .calc__icon {
            font-size: 18px;
            margin-left: auto;
            margin-right: 10px;
        }
        .calc__price {
            font-size: 35px;
            padding-top: 7px;
            padding-bottom: 7px;
            text-align: right;
            padding-right: 10px;
        }
        .calc__sum {
            color: $color-main;
        }
        .calc__area {
            display: flex;
            align-items: left;
            justify-content: flex-start;
            flex-direction: row;
        }
        .calc__input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #cdcdcd;
            border-radius: 10px;
            color: #201e34;
        }
        .calc__sup {
            padding-left: 5px;
        }
        .calc__services-list {
            overflow-y: auto;
            background-color: #fff;
            padding: 10px;
            border-radius: 7px;
            margin-top: 1px;
            border: 1px solid #cdcdcd;
            height: 65%;
        }
        .calc__holder {
            margin-top: auto;
        }
        .control {
            display: block;
            position: relative;
            padding-left: 30px;
            margin-bottom: 5px;
            padding-top: 3px;
            cursor: pointer;
            font-size: 15px;
        }

        .control input {
            position: absolute;
            z-index: -1;
            opacity: 0;
        }

        .control_indicator {
            position: absolute;
            top: 2px;
            left: 0;
            height: 20px;
            width: 20px;
            background: #fff;
            //border: 0 solid #000000;
            border: 1px solid #aaa;
            border-radius: 50%;
        }

        .control-radio .control_indicator {
            border-radius: 0;
        }

        .control:hover input ~ .control_indicator,
        .control input:focus ~ .control_indicator {
            background: #fff;
        }

        .control input:checked ~ .control_indicator {
            background: $color-main;
        }

        .control:hover input:not([disabled]):checked ~ .control_indicator,
        .control input:checked:focus ~ .control_indicator {
            background: $color-main;
        }

        .control input:disabled ~ .control_indicator {
            background: #fff;
            //opacity: 0.6;
            pointer-events: none;
        }

        .control_indicator:after {
            box-sizing: unset;
            content: '';
            position: absolute;
            display: none;
        }

        .control input:checked ~ .control_indicator:after {
            display: block;
        }

        .control-checkbox .control_indicator:after {
            left: 7px;
            top: 3px;
            width: 3px;
            height: 8px;
            border: solid #ffffff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .control-checkbox input:disabled ~ .control_indicator:after {
            border-color: #7b7b7b;
        }
    }
</style>
