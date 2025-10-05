<?php

namespace App\Filament\Pharmacy\Resources\Communities;

use App\Filament\Pharmacy\Resources\Communities\Pages\CreateCommunity;
use App\Filament\Pharmacy\Resources\Communities\Pages\EditCommunity;
use App\Filament\Pharmacy\Resources\Communities\Pages\ListCommunities;
use App\Filament\Pharmacy\Resources\Communities\Schemas\CommunityForm;
use App\Filament\Pharmacy\Resources\Communities\Tables\CommunitiesTable;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Pharmacy\Domain\Models\Community;
use Src\Shared\Domain\Models\User;

class CommunityResource extends Resource
{
    protected static ?string $model = Community::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        // Pharmacists can see their personal communities AND communities of their pharmacy
        return parent::getEloquentQuery()
            ->where(function (Builder $query) {
                $user = Filament::auth()->user();
                $query->where(function ($q) use ($user) {
                    $q->where('owner_type', User::class)->where('owner_id', $user->id);
                })->orWhere(function ($q) use ($user) {
                    $q->where('owner_type', \Src\Pharmacy\Domain\Models\Pharmacy::class)->where('owner_id', $user->pharmacy_id);
                });
            });
    }

    public static function canCreate(): bool
    {
        // Everyone can at least try to create their first free one.
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return CommunityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommunitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommunities::route('/'),
            'create' => CreateCommunity::route('/create'),
            'edit' => EditCommunity::route('/{record}/edit'),
        ];
    }

    // /**
    //  * SECURITY SCOPING: Ensures pharmacists only see communities they are a member of.
    //  */
    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()
    //         ->whereHas('users', fn (Builder $query) => $query->where('user_id', Filament::auth()->id()));
    // }

    /**
     * BUSINESS LOGIC: Attaches the creator to the community and sets the pharmacy.
     */
    protected function handleRecordCreation(array $data): Community
    {
        $user = Filament::auth()->user();
        $data['pharmacy_id'] = $user->pharmacy_id;

        $community = static::getModel()::create($data);

        // Automatically add the creator to the team and sync other selected members.
        $teamMembers = $data['users'] ?? [];
        $teamMembers[] = $user->id;
        $community->users()->sync(array_unique($teamMembers));

        return $community;
    }

    // /**
    //  * MONETIZATION GATE: Checks the pharmacy's subscription plan before allowing creation.
    //  */
    // public static function canCreate(): bool
    // {
    //     $user = Filament::auth()->user();

    //     if (! $user?->pharmacy?->hasActiveSubscription()) {
    //         return false;
    //     }

    //     $limit = $user->pharmacy->getPlanFeature('limits.max_communities', 0);
    //     $currentCount = $user->pharmacy->communities()->count();

    //     return $currentCount < $limit;
    // }
}
