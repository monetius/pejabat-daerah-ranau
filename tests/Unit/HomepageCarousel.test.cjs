const fs=require('node:fs');const vm=require('node:vm');const assert=require('node:assert/strict');const {test}=require('node:test');
const file=require('node:path').resolve(__dirname, '../../public/assets/js/kinomulok.js');
const source=fs.readFileSync(file,'utf8').split('function initPdCarousel() {')[1].split('\nfunction initCardBlur()')[0];
function fixture(){
 const element=()=>({handlers:{},dataset:{},classList:{add(){},remove(){},contains(){return false;}},setAttribute(k,v){this[k]=v;},addEventListener(k,fn){this.handlers[k]=fn;},focus(){},getAttribute(){return '/example';}});
 const cards=Array.from({length:6},(_,i)=>({...element(),dataset:{title:'Slide '+i,desc:'Description '+i}}));
 const parts=Object.fromEntries(['.pd-info','.pd-title','.pd-desc','.pd-link','.pd-credit','.pd-count','.pd-prev','.pd-next','.pd-cards'].map(k=>[k,element()]));
 const root={...element(),querySelectorAll:()=>cards,querySelector:k=>parts[k]};
 vm.runInNewContext('function initPdCarousel() {'+source+'\ninitPdCarousel();',{document:{getElementById:()=>root},setTimeout:()=>1,clearTimeout(){},Date});
 const swipe=(dx,dy)=>{parts['.pd-cards'].handlers.touchstart({touches:[{clientX:200,clientY:200}]});parts['.pd-cards'].handlers.touchend({changedTouches:[{clientX:200+dx,clientY:200+dy}]});};
 return {parts,cards,root,swipe};
}
test('next, previous and wrap-around keep text and link synchronized',()=>{const {parts,cards}=fixture();parts['.pd-prev'].handlers.click();assert.equal(parts['.pd-title'].textContent,'Slide 5');parts['.pd-next'].handlers.click();assert.equal(cards[0].dataset.pos,'0');assert.equal(parts['.pd-count'].textContent,'01 / 06');});
test('vertical and diagonal scroll do not change slides',()=>{const {parts,swipe}=fixture();swipe(60,150);assert.equal(parts['.pd-count'].textContent,'01 / 06');swipe(60,60);assert.equal(parts['.pd-count'].textContent,'01 / 06');});
test('horizontal swipe advances without following the card link',()=>{const {parts,swipe}=fixture();swipe(-90,8);assert.equal(parts['.pd-count'].textContent,'02 / 06');let blocked=false;parts['.pd-cards'].handlers.click({preventDefault(){blocked=true;},stopPropagation(){}});assert.equal(blocked,true);});
test('cancelled touch does not change a slide',()=>{const {parts}=fixture();const stage=parts['.pd-cards'];stage.handlers.touchstart({touches:[{clientX:200,clientY:200}]});stage.handlers.touchcancel();stage.handlers.touchend({changedTouches:[{clientX:20,clientY:200}]});assert.equal(parts['.pd-count'].textContent,'01 / 06');});
