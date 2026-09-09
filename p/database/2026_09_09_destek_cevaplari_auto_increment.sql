-- destek_cevaplari.reply_id PRIMARY KEY olarak tanımlıydı ama AUTO_INCREMENT
-- eksikti. Sonuç: her INSERT "Field 'reply_id' doesn't have a default value"
-- hatasıyla sessizce başarısız oluyordu (Database sınıfı hatayı yutuyor),
-- destek talebi yanıtları hem admin hem müşteri tarafında hiç kaydedilmiyordu.

ALTER TABLE destek_cevaplari MODIFY reply_id BIGINT(20) NOT NULL AUTO_INCREMENT;
