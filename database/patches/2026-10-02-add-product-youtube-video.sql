-- Optional YouTube video shown as an item in the product media gallery.
ALTER TABLE `products`
    ADD COLUMN `youtube_video_id` VARCHAR(11) NULL DEFAULT NULL;
