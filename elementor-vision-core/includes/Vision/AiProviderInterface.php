<?php

namespace ElementorVisionCore\Vision;

interface AiProviderInterface {
    public function analyze_image(string $image_data_url, string $prompt): array;
}
