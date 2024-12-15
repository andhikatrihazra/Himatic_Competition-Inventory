<div class="p-6">
    <!-- Category Name -->
    <h2 class="text-3xl font-semibold">{{ $category->name }}</h2>

    <!-- Category Description -->
    <p class="mt-2 text-lg">{{ $category->description ?? 'No description available.' }}</p>

    <!-- Related Products -->
    <div class="mt-6">
        <h3 class="text-xl font-semibold">Produk yang Terhubung:</h3>

        <!-- Livewire component for displaying products related to the category -->
        @livewire('filament.resources.category-resource.relation-managers.products-relation-manager', ['record' => $category])
    </div>
</div>
