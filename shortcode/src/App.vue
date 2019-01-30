<template>
    <div class="calc__form">
        <div v-if="info.loading" class="calc__preloader">
            <div class="calc__loader"></div>
        </div>
        <div v-else class="calc__box">
            <div v-show="!info.nextStage" class="calc__row">
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
                                         class="calc__dropdown calc__dropdown--number"
                                         :allow-empty="false"></multiselect>
                        </div>
                        <div v-else>
                            <div class="calc__head">Площадь уборки</div>
                            <div class="calc__area">
                                <input type="number" class="calc__input" :min="cleaningArea.min" :max="cleaningArea.max"
                                       v-model="cleaningArea.value">
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
                    <div class="calc__services-list" v-show="additionalServices.show">
                        <label class="control control-checkbox calc__label" v-for="item in additionalServices.data"
                               :key="item.id" @click="changeIndex(item.id)">
                            {{ item.name }}
                            <input type="checkbox" v-if="additionalServices.checkedIndex[item.id]" checked>
                            <input type="checkbox" v-else>
                            <div class="control_indicator"></div>
                        </label>
                    </div>
                </transition>
            </div>

            <div v-show="info.nextStage">
                <label for="calc__name" class="calc__label">Имя</label>
                <input id="calc__name" value=""
                       :class="{'calc__input': true, 'calc__input--name': true, 'is-danger': errors.has('calc__name') }"
                       placeholder="Представьтесь" v-model="contact.name"
                       v-validate.disable="'required'"
                       name="calc__name">
                <label for="calc__phone" class="calc__label">Телефон</label>
                <input id="calc__phone"
                       :class="{'calc__input': true, 'calc__input--phone': true, 'is-danger': errors.has('calc__phone') }"
                       placeholder="Ваш номер" v-model="contact.phone" ref="phone"
                       v-validate.disable="'required'" name="calc__phone" v-mask="'+7 (999) 999 99 99'">
                <label class="control control-checkbox">
                    Я согласен(а) на обработку моих персональных данных
                    <input type="checkbox" @change="changeDisable" ref="controlCheckbox"/>
                    <div class="control_indicator"></div>
                </label>
            </div>

            <div class="calc__holder">
                <div class="calc__price" v-if="!info.nextStage">от <span class="calc__sum">{{animatedNumber}}</span> ₽
                </div>
                <button type="button" :class="['btn', 'btn--result', 'hvr-pop', {'is-disable': btnResult.isDisable}]"
                        @click.prevent="btnResult.actionFunct" ref="btnResult"> {{btnResult.title}}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
  import Multiselect from 'vue-multiselect'
  import VueResource from 'vue-resource'
  import { TweenLite } from 'gsap'
  import VeeValidate from 'vee-validate'
  import VueSweetalert2 from 'vue-sweetalert2'

  Vue.use(VueSweetalert2)
  const VueInputMask = require('vue-inputmask').default

  let _ = require('lodash')

  Vue.component('multiselect', Multiselect)
  Vue.use(VueResource)
  Vue.use(VeeValidate)
  Vue.use(VueInputMask)

  export default {
    name: 'app',
    data () {
      return {
        info: {
          data: null,
          loading: true,
          nextStage: false,
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
        tweenedNumber: 0,
        btnResult: {
          actionFunct: this.orderAction,
          title: 'Заказать',
          isDisable: false
        },
        contact: {
          name: '',
          phone: ''
        },
        objAlertResult: {
          title: '',
          message: '',
          type: '',
          customCloseBtnClass: 'btn btn--modal',
          customCloseBtnText: 'Ok'
        }
      }
    },
    computed: {
      sum: function () {
        //функция расчёта цены за работы клининга
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
      //загружаем данные из json файла (в основном цены) при монтированни формы
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
          this.cleaningArea.min = +this.info.data.cleaningArea.min
          this.cleaningArea.max = +this.info.data.cleaningArea.max

          //устанавливаем дополнительные услуги
          this.additionalServices.data = this.info.data.additionalServices
          this.additionalServices.checkedIndex = Array(this.additionalServices.data.length).fill(false)

          // this.demoStage()

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
      //Меняем индекс в массиве дополнительных услуги и через splice, т.е. прямое изменение элемента массива нереактивно.
      changeIndex: _.debounce(function (index) {
        this.additionalServices.checkedIndex.splice(index, 1, !this.additionalServices.checkedIndex[index])
      }, 50),
      orderAction: function () {
        //при нажатии на кнопку "Заказать" происходит проверка находится ли площадь помещения в указанном интервале.
        // Если нет, то выводиться сообщение об ошибке
        if (+this.objectCleaning.selected.id !== 0) {
          const areaValue = this.cleaningArea.value
          const min = this.cleaningArea.min
          const max = this.cleaningArea.max
          if (areaValue >= min && areaValue <= max) {
            this.nextStageStep()
          } else {
            const Toast = this.$swal.mixin({
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 4000
            })

            Toast.fire({
              type: 'error',
              title: `Площадь уборки должна быть от ${min} до ${max} м^2`
            })
          }
        } else {
          this.nextStageStep()
        }
      },
      nextStageStep: function () {
        //переход к следующему этапу
        this.info.nextStage = true
        this.btnResult.actionFunct = this.sendAction
        this.btnResult.title = 'Отправить'
        this.btnResult.isDisable = true
      },
      sendAction: function () {
        //Процедура отправки данных на сервер.
        //Проводим валидацию, заполняем данные, получаем ответ успешный или провальный
        this.$validator.validateAll()
          .then((result) => {
            if (result) {
              let formData = new FormData()
              formData.append('action', 'floor_add')
              formData.append('nonce', wp_data.nonce)
              formData.append('name', this.contact.name)
              formData.append('phone', this.contact.phone)
              formData.append('objectCleaning', this.objectCleaning.selected.name)
              if (+this.objectCleaning.selected.id === 0) {
                formData.append('numberOfRooms', this.numberOfRooms.selected.name)
              } else {
                formData.append('cleaningArea', this.cleaningArea.value)
              }
              formData.append('periodicity', this.periodicity.selected.name)
              formData.append('cleaningType', this.cleaningType.selected.name)

              let j = 0
              this.additionalServices.checkedIndex.forEach((item, i) => {
                if (item) {
                  formData.append(`additionalServices${j}`, this.additionalServices.data[i].name)
                  j++
                }
              })
              if (j !== 0) {
                formData.append('additionalServicesCount', j)
              }
              formData.append('sum', this.sum)

              this.$http.post(wp_data.url_ajax, formData)
                .then(response => {
                  let answer = response.data
                  // console.log(answer)
                  if (answer.success) {
                    this.$swal.fire({
                      type: 'success',
                      title: 'Спасибо за заказ!',
                      text: answer.data,
                      confirmButtonColor: '#F25E99'
                    })
                  } else {
                    let message = ''
                    answer.data.forEach((element) => {
                      message += element
                    })
                    this.$swal.fire({
                      type: 'error', title: 'Ошибка', text: message,
                      confirmButtonColor: '#F25E99'
                    })
                  }
                  this.firstStage()
                }, response => {
                  this.$swal.fire({
                    type: 'error', title: 'Ошибка', text: response.statusText,
                    confirmButtonColor: '#F25E99'
                  })
                  this.firstStage()
                })
            } else {
              console.log('Ошибка при заполнении данных!')
            }
          })
      },
      demoStage: function () {
        // this.btnResult.actionFunct = this.sendAction
        // this.btnResult.title = 'Отправить'
        // this.info.nextStage = true
        this.contact.name = 'Иван Иванов'
        this.contact.phone = '+7 (111) 111 11 11'
        this.btnResult.isDisable = false
      },
      firstStage: function () {
        this.info.nextStage = false
        this.btnResult.title = 'Заказать'
        this.btnResult.actionFunct = this.orderAction
        this.btnResult.isDisable = false
        this.$refs.controlCheckbox.checked = false
        this.additionalServices.show = false
      },
      changeDisable: function () {
        this.btnResult.isDisable = !this.btnResult.isDisable
      }
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
            display: inline-block;
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #cdcdcd;
            border-radius: 10px;
            color: #201e34;
            vertical-align: middle;
            &::placeholder {
                color: #ddd;
            }
            &:focus {
                border-color: $color-main;
                outline: 0;
                box-shadow: 0 0 3px $color-main;
                background-color: white;
            }
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
            height: 90%;
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
        .calc__label {
            display: inline-block;
            font-weight: 700;
            font-size: 15px;
            vertical-align: middle;
            padding-bottom: 7px;
        }
        .calc__input--name, .calc__input--phone {
            margin-bottom: 7px;
        }
        input.is-danger {
            border-color: red;
            box-shadow: 0 0 3px red;
        }
        .calc__row {
            height: 67%;
        }
        .is-disable {
            pointer-events: none;
            opacity: .6;
        }
    }

    #swal2-title {
        display: block !important;
    }
</style>
