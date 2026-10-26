<?php
/**
 * Canlı (stored olmayan) admin panel bildirimleri.
 *
 * bildirimler tablosuna yazılmaz; her çağrıda güncel durum sorgulanır,
 * koşul ortadan kalkınca (süre uzatılınca, tarih geçince) kendiliğinden
 * kaybolur. "Okundu" ile işaretlenemez — notification_id=0 ile ayırt
 * edilir (bkz. p/config/header.php ve p/dist/api/notifications_api.php).
 */
if (!function_exists('prolyn_canli_bildirimler')) {
    function prolyn_canli_bildirimler(Database $db): array
    {
        $sonuc = [];

        try {
            if (!method_exists($db, 'tableExists') || !$db->tableExists('abonelikler')) {
                return $sonuc;
            }

            $yeniAbonelikler = $db->getRowsAssoc(
                "SELECT a.subscription_id, a.plan_name, a.created_at, k.company_name
                 FROM abonelikler a
                 LEFT JOIN kurumlar k ON a.customer_id = k.customer_id
                 WHERE a.created_at >= NOW() - INTERVAL 2 DAY
                 ORDER BY a.created_at DESC
                 LIMIT 5"
            );
            foreach ($yeniAbonelikler as $ab) {
                $sonuc[] = [
                    'notification_id' => 0,
                    'title' => 'Yeni Abonelik',
                    'message' => ($ab['company_name'] ?? 'Bilinmeyen Firma') . ' — ' . (string)$ab['plan_name'] . ' paketine kaydoldu.',
                    'type' => 'success',
                    'action_url' => 'crm/subscriptions.php',
                    'created_at' => $ab['created_at'],
                ];
            }

            $yaklasanAbonelikler = $db->getRowsAssoc(
                "SELECT a.subscription_id, a.end_date, k.company_name
                 FROM abonelikler a
                 LEFT JOIN kurumlar k ON a.customer_id = k.customer_id
                 WHERE a.status = 'active' AND a.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                 ORDER BY a.end_date ASC
                 LIMIT 5"
            );
            foreach ($yaklasanAbonelikler as $ab) {
                $kalanGun = (int)floor((strtotime($ab['end_date']) - strtotime(date('Y-m-d'))) / 86400);
                $sonuc[] = [
                    'notification_id' => 0,
                    'title' => 'Abonelik Süresi Yaklaşıyor',
                    'message' => ($ab['company_name'] ?? 'Bilinmeyen Firma') . ' aboneliği ' . $kalanGun . ' gün içinde (' . date('d.m.Y', strtotime($ab['end_date'])) . ') sona eriyor.',
                    'type' => 'warning',
                    'action_url' => 'crm/subscriptions.php',
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        } catch (Throwable $e) {
            // Canlı bildirimler hesaplanamazsa sessizce boş dönülür
        }

        return $sonuc;
    }
}
