import { defineCollection, z } from 'astro:content';

const feeProcessSchema = z.object({
  asanaFormUrl: z.string().url().nullable(),
  walkInOnly: z.boolean().default(false),
  notes: z.string().optional(),
});

const professionCollection = defineCollection({
  type: 'content',
  schema: z.object({
    name: z.string(),
    regulator: z.enum(['NYSED', 'OASAS', 'AAMA']),
    form1Pdf: z.string().nullable(),
    form1Revision: z.string().nullable(),
    licensureFee: feeProcessSchema.nullable(),
    examFee: feeProcessSchema.nullable(),
    checklistUrl: z.string().url().nullable(),
    needsContentReview: z.boolean().default(false),
    reviewNotes: z.string().optional(),
    order: z.number(),
  }),
});

const pageCollection = defineCollection({
  type: 'content',
  schema: z.object({
    title: z.string().optional(),
    section: z.string(),
  }),
});

export const collections = {
  professions: professionCollection,
  page: pageCollection,
};
