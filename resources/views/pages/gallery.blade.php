<x-layouts.site title="Gallery" active="gallery" description="Photos of the Wisdom Nursery team, plants, nursery and deliveries.">
    <x-page-hero eyebrow="Gallery" title="Our team, plants and nursery." :image="site_image('image_gallery_banner') ?? site_image('image_facility_1')" :srcset="site_image_srcset('image_gallery_banner')"
        intro="Photos of the people, the plants and the place behind Wisdom Nursery." />
    <section class="container-page py-16 lg:py-24">
        @include('partials.gallery', ['title' => 'All photos'])
    </section>
</x-layouts.site>
