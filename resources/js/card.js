import GlobalFilterCard from './components/GlobalFilterCard'
import SectionHeader from './components/SectionHeader'

Nova.booting((app, store) => {
  app.component('nova-global-filter', GlobalFilterCard)
  app.component('nova-section-header', SectionHeader)
})
