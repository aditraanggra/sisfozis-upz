<?php

namespace App\Services;

use App\Models\AllocationConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AllocationConfigService
{
    private const CACHE_KEY = 'allocation_configs';
    private const CACHE_TTL = 86400; // 24 hours

    /**
     * Get all allocation percentages for a ZIS type and date
     *
     * Returns array with setor, kelola, amil, and penyaluran percentages.
     * When kelola is 0 (100% setor), amil and penyaluran are effectively 0
     * for calculation purposes, though the configured amil_percentage is preserved.
     */
    public function getAllocation(string $zisType, Carbon|string $date): array
    {
        $year = $date instanceof Carbon ? $date->year : Carbon::parse($date)->year;
        $config = $this->getConfigForYear($zisType, $year);

        $penyaluran = number_format(100 - (float) $config['amil_percentage'], 2, '.', '');

        return [
            'setor' => $config['setor_percentage'],
            'kelola' => $config['kelola_percentage'],
            'amil' => $config['amil_percentage'],
            'penyaluran' => $penyaluran,
        ];
    }

    /**
     * Get setor percentage for a ZIS type and date
     */
    public function getSetorPercentage(string $zisType, Carbon|string $date): string
    {
        return $this->getAllocation($zisType, $date)['setor'];
    }

    /**
     * Get kelola percentage for a ZIS type and date
     */
    public function getKelolaPercentage(string $zisType, Carbon|string $date): string
    {
        return $this->getAllocation($zisType, $date)['kelola'];
    }

    /**
     * Get amil percentage for a ZIS type and date
     */
    public function getAmilPercentage(string $zisType, Carbon|string $date): string
    {
        return $this->getAllocation($zisType, $date)['amil'];
    }

    /**
     * Get penyaluran percentage (100 - amil) for a ZIS type and date
     */
    public function getPenyaluranPercentage(string $zisType, Carbon|string $date): string
    {
        return $this->getAllocation($zisType, $date)['penyaluran'];
    }

    /**
     * Calculate actual hak_amil amount from kelola funds
     * Returns 0 when kelola is 0 (Requirement 10.3)
     */
    public function calculateHakAmil(string $kelolaAmount, string $amilPercentage, int $scale = 2): string
    {
        $kelola = (float) $kelolaAmount;
        if ($kelola == 0) {
            return '0';
        }
        $result = ($kelola * (float) $amilPercentage) / 100;
        return number_format($result, $scale, '.', '');
    }

    /**
     * Calculate actual penyaluran_pendis amount from kelola funds
     * Returns 0 when kelola is 0 (Requirement 10.3)
     */
    public function calculatePenyaluran(string $kelolaAmount, string $hakAmilAmount, int $scale = 2): string
    {
        $kelola = (float) $kelolaAmount;
        if ($kelola == 0) {
            return '0';
        }
        $result = $kelola - (float) $hakAmilAmount;
        return number_format($result, $scale, '.', '');
    }

    /**
     * Get configuration for a specific year (finds most recent rule <= year)
     */
    protected function getConfigForYear(string $zisType, int $year): array
    {
        $configs = $this->getCachedConfigs();

        // Filter configs for this ZIS type and find the applicable one
        $applicable = collect($configs)
            ->filter(fn($c) => $c['zis_type'] === $zisType && $c['effective_year'] <= $year)
            ->sortByDesc('effective_year')
            ->first();

        if ($applicable) {
            return $applicable;
        }

        // Return defaults if no config found
        return $this->getDefaults($zisType);
    }

    /**
     * Get cached configurations or load from database
     */
    protected function getCachedConfigs(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return AllocationConfig::all()->toArray();
        });
    }

    /**
     * Clear the configuration cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get default percentages for a ZIS type
     * Matches current hardcoded values for backward compatibility (Requirement 2.3, 8.2)
     */
    protected function getDefaults(string $zisType): array
    {
        $amilDefault = $zisType === AllocationConfig::TYPE_IFS
            ? AllocationConfig::DEFAULT_AMIL_IFS
            : AllocationConfig::DEFAULT_AMIL_ZF_ZM;

        return [
            'zis_type' => $zisType,
            'effective_year' => 2000, // Far past year as fallback
            'setor_percentage' => (string) AllocationConfig::DEFAULT_SETOR,
            'kelola_percentage' => (string) AllocationConfig::DEFAULT_KELOLA,
            'amil_percentage' => (string) $amilDefault,
        ];
    }
}
